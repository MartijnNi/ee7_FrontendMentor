<?php
/**
 * This file contains helper functions to simplify development
 * 
 */

namespace {{namespace}}\Core\Traits;

/**
 * Add-on Service
 */
trait HandlesForms
{
    public function sanitizePost($post): object
    {
        $post = (object) $post; // Convert input to an object
        try {
            // Sanitize input
            foreach ($post as $key => $value) {
                if (!empty($value) && is_array($value)) {
                    // Handle nested arrays recursively by converting to an object
                    $post->$key = $this->sanitizePost((object) $value);
                } else {
                    // $value = ee()->input->post($key);
                    try {
                        // Attempt to decode encrypted values
                        if (@ee('Encrypt')->decode($value)) {
                            $value = @ee('Encrypt')->decode($value);
                        }
                    } catch (\Throwable $e) {
                        // Handle exception if needed
                    }
                    
                    if (ee('{{addonName}}:Helper')->isSerialized($value)) {
                        $value = unserialize($value);
                    }
                    
                    if (is_string($value)) {
                    $value = ee('Security/XSS')->clean($value);
                    }
                    
                    // Assign the sanitized value back to the object
                    $post->$key = $value;
        
                    // Remove values that start with '{' and end with '}'
                    if (is_string($value) && strpos($value, '{') === 0 && strrpos($value, '}') === strlen($value) - 1) {
                        unset($post->$key);
                    }
                }
            }
        
            // Check if the 'data' property exists and is an array
            if (isset($post->data) && is_array($post->data)) {
                // Loop through 'data' and assign its key-value pairs to the top level
                foreach ($post->data as $key => $value) {
                    if ($key == 'rules') {
                        $value = unserialize(base64_decode($value));
                    }
                    $post->data[$key] = $value;
                }
            }
        } catch (\Exception $e) {
            ee()->logger->developer('Error sanitizing POST data: '. $e->getMessage());
        }
        return $post;
    }
  }