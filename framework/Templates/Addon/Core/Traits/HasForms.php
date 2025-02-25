<?php
/**
 * This file contains helper functions to simplify development
 * 
 */

namespace {{namespace}}\Core\Traits;

/**
 * Add-on Service
 */
trait HasForms
{
    protected $form;

    protected function makeForm($tag, $action)
    {
        $this->initializeForm();
        $this->populateFormErrors();

        $tagdata = $tag->tagdata();
        $form_details = $tag->tagparams();
        $form_details['action'] = $action;
        $form_details['secure'] = TRUE;
        $form_details['id'] = $this->form->id;
        $form_details['secure:id'] = $form_details['id'];
        $form_details['hidden_fields'] = $this->prepareHiddenFields($form_details);        

        if (isset($form_details['output']) && $form_details['output'] == 'n') {
            return $form_details;
        }

        $form_open = ee()->functions->form_declaration($form_details);
        $form_close = form_close();
        $tagdata = $form_open . $tagdata . $form_close;
        
        return $tagdata;
    }
    
    public function getFormErrors()
    {
        // dd(ee()->TMPL);
        $flashedErrors = ee()->session->flashdata($this->addonName.'_validation_errors_' . $this->form->id);
        if (!$flashedErrors)
            return false;
        $errors = [];
        foreach ($flashedErrors as $name => $message) {
            $errors[] = [
                'field' => $name,
                'message' => $message
            ];
        }
        return $errors;
    }

    public function addErrorVariables($index, $tag)  {
        $errors = $this->helpers->variables($this->form->errors, 'errors:');        
        $tag->setVariable($index, 'has_errors', $errors ? TRUE : FALSE);
        $tag->setVariable($index, 'errors', $errors);
        if (!empty($this->form->errors)) {
            foreach ($this->form->errors as $formError) {
                $tag->setVariable(
                    $index, 
                    'error_'.$formError['field'], 
                    $formError['message']
                );
            }
        }     
    }
    public function addFormVariables($index, $tag) {
        $this->addErrorVariables($index, $tag);   
        $tag->setVariable($index, 'is_valid', $this->form->success ? TRUE : FALSE);
    }
 

    private function initializeForm()
    {
        $this->form = new \stdClass();
        $this->form->id = '';
        $this->form->count = 0;
        // Retrieve or initialize the static counter
        $this->form->count =
            ee()->session->cache($this->addonName, 'form_id_counter') ? ee()->session->cache($this->addonName, 'form_id_counter') : 0;
        $this->form->count++;
        ee()->session->set_cache($this->addonName, 'form_id_counter', $this->form->count);
        $this->form->id = empty(ee()->TMPL->form_id) ? $this->addonName.'_form_' . $this->form->count : ee()->TMPL->form_id;

        $this->form->success = ee()->session->flashdata($this->addonName.'_validation_success_' . $this->form->id);
    }

    private function populateFormErrors()
    {
        $errors = $this->getFormErrors();
        if ($errors) {
            $this->form->errors = $errors;
        } else {
            $this->form->errors = [];
        }
    }

    private function prepareHiddenFields($form_details)
    {
        $fields = [];
        if (!empty($form_details)) {
            // Set Hidden Fields
            $hidden_fields = [];
            $rules = [];
            $secure = [];
            $attachments = [];
        
            foreach ($form_details as $key => $value) {
                if (strpos($key, 'hidden:') === 0) {
                    $hidden_fields[substr($key, strlen('hidden:'))] = $value;
                }
                if (strpos($key, 'rules:') === 0) {
                    $rules[substr($key, strlen('rules:'))] = $value;
                }
                if (strpos($key, 'secure:') === 0) {
                    $secure[substr($key, strlen('secure:'))] = $value;
                }
                if (strpos($key, 'secure_storage:') === 0) {
                    $secure['storage'][substr($key, strlen('secure_storage:'))] = $value;
                }
                if (strpos($key, 'attachment') === 0) {
                    $attachments = explode("|", $value);
                }
            }
        
            $hidden_fields = array_filter($hidden_fields);
            $rules = array_filter($rules);
            $data = [];
        
            $fields = $hidden_fields;
            if (!empty($secure)) {
                $data = $secure;
            }
    
            if (!empty($rules)) {
                $data['rules'] = base64_encode(serialize($rules));
            }
        
            if (isset($form_details['return_url'])) {
                $data['return_url'] = $form_details['return_url'];
            }
            if (isset($form_details['error_url'])) {
                $data['error_url'] = $form_details['error_url'];
            }
            if (!empty($attachments)) {
                $attachmentsInfo = [];
                foreach ($attachments as $attachment) {
                    $info = $this->helper->getFileInfoFromURL($attachment);
                    if ($info) {
                        $attachmentsInfo[] = $info;
                    }
                }
                $data['attachments'] = $attachmentsInfo;
            }
            // // Generate a signature
            // $data['signature'] = $this->generateSignature(serialize($data), $form_details['id']);
    
            // Encrypt the data payload
            $fields['data'] = ee('Encrypt')->encode(serialize($data));
        }
    
        return $fields;
    }

    private function generateSignature(string $payload, string $secret_key): string
    {
        // Get the current Unix timestamp using ExpressionEngine's Localize class
        $now = ee()->localize->now;
        
        // Create metadata with start and end times
        // 'start_date' is the current timestamp
        // 'end_date' is 1 hour (3600 seconds) added to the current timestamp
        $meta = [
            'start_date' => $now,               // The timestamp when the signature becomes valid
            'end_date' => $now + (1 * 60 * 60), // The timestamp when the signature expires
            'referer' => base_url()             // The base URL as the referer (for security)
        ];
        
        // JSON encode the metadata to make it a string, and base64url encode it
        $encodedMeta = $this->base64UrlEncode(json_encode($meta));
        
        // Generate the HMAC-SHA256 signature:
        // - Use HMAC with SHA256 to hash the payload using the secret key
        // - Append the base64url-encoded metadata
        return hash_hmac('sha256', $payload, $secret_key) . '.' . $encodedMeta;
    }
    
    /**
     * Base64url encode a string (similar to Base64, but replaces '+' with '-' and '/' with '_')
     * and removes padding ('=').
     */
    private function base64UrlEncode(string $data): string
    {
        // Base64 encode the data and replace special characters
        $base64 = base64_encode($data);
        
        // Replace '+' with '-', '/' with '_', and remove any padding ('=')
        return rtrim(strtr($base64, '+/', '-_'), '=');
    }
    
    
    
}
// EOF
