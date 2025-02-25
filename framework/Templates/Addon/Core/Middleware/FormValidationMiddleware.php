<?php
namespace {{namespace}}\Core\Middleware;

use {{namespace}}\Core\Http\Request;
use {{namespace}}\Core\Base\Middleware;

class FormValidationMiddleware extends Middleware
{
    private $post = [];

    public function handle(Request $request): Request
    {
        if ($request->isGet()) {
            return $request; // No processing needed for GET requests
        }
        
        try {
            $this->post = (array) $this->sanitizePost($request->data());
            $errorUrl = $this->post['error_url'] ?? ee()->functions->form_backtrack('-1');
            $data = $this->post['data'] ?? [];
            $id = $data['id'] ?? 'unknown';
            
            $errors = $this->validateForm($request, $data, $id);
           
            if (!empty($errors)) {
                return $this->handleErrors($request, $errors, $errorUrl, $id);
            }

            if (!$request->isAjax()) {
                ee()->session->set_flashdata($this->addonName.'_validation_success_' . $id, true);
            }
        } catch (\Throwable $e) {
            ee()->logger->developer('Invalid post request to FormValidationMiddleware: ' . $e->getMessage());
            if ($request->isAjax()) {
                return $this->response->error();
            }

            $this->setErrorFlashData($id, ['unknown' => lang('An unexpected error occurred.')]);
            return redirect(ee()->functions->form_backtrack('-1'));
        }

        return $request;
    }

    private function validateForm(Request $request, array $data, string $id): array
    {
        $errors = [];
        
        // Validate CSRF token
        if (CSRF_TOKEN != $request->csrf()) {
            $errors['message'] = lang('Invalid request');
            // $errors['csrf_token'] = lang('Invalid or missing CSRF token.');
        }

        // Validate rules
        $rules = (array) ($data['rules'] ?? []);
        $validation = ee('Validation')->make($rules)->validate($this->post);

        if ($validation->isNotValid()) {
            foreach ($validation->getAllErrors() as $field => $error) {
                $errors[$field] = reset($error); // Use the first error message
            }
        }

        return $errors;
    }

    private function handleErrors(Request $request, array $errors, string $errorUrl, string $id)
    {
        if ($request->isAjax()) {
            return $this->response->error('An error occurred', $errors);
        }

        $this->setErrorFlashData($id, $errors);
        ee()->session->set_flashdata($this->addonName.'_validation_success_' . $id, false);

        return redirect($errorUrl);
    }

    private function setErrorFlashData(string $id, array $errors): void
    {
        ee()->session->set_flashdata($this->addonName.'_validation_errors_' . $id, $errors);
    }
    
}

