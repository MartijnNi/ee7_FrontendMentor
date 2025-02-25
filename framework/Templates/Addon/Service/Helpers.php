<?php
/**
 * This file contains helper functions to simplify development
 * 
 */

namespace {{namespace}}\Service;

/**
 * Add-on Service
 */
class Helpers
{
    /**
     * Flattens a multi-dimensional array into a single-level array.
     *
     * @param array $array         The input multi-dimensional array to flatten.
     * @param bool  $keepNamedKeys  Whether to use named keys (with a colon separator) for nested keys.
     * @param string $prefix        The prefix used for creating named keys (if enabled).
     *
     * @return array               The flattened array with either named or indexed keys.
     */
    public static function flatten(array $array) 
    {
        $result = [];
      
        foreach ($array as $item) {
            if (is_array($item)) {
                $result = array_merge($result, self::flatten($item));
            } else {
                $result[] = $item;
            }
        }
    
        return $result;
    }
    /**
     * Compresses a multi-dimensional array, preserving the top key as the named key of the nested value.
     *
     * Ideal for the validation service $result->getAllErrors() array to return a key value pair.
     *
     * @param array $array  The multi-dimensional array to compress.
     *
     * @return array         The compressed array with the top key as the named key of the nested value.
     */
    public static function compress(array $array, $level = 0) 
    {
      $result = []; 
      foreach ($array as $key => $value) {
          if (is_array($value)) {
              $flattened = self::flatten($value);
              foreach ($flattened as $nestedValue) {
                  $result[$key] = $nestedValue;
              }
          } else {
              $result[$key] = $value;
          }
      }
      return $result;
    }
    /**
     * Prefixes keys in an associative array with a given prefix based on data type.
     *
     * @param array $variables Associative array to be processed.
     * @param string $prefix Prefix to be added to keys.
     *
     * @return array Processed associative array with prefixed keys.
     */
    public static function prefix($variables, $prefix) {
        $result = [];

        foreach ($variables as $key => $value) {
            // Check if the key should be prefixed based on data type
            if (is_string($key)) {
                $newKey = $prefix . $key;
            } else {
                $newKey = $key;
            }

            // Recursively process arrays
            $result[$newKey] = is_array($value) ? self::prefix($value, is_string($key)?$prefix . $key.':':$prefix) : $value;
        }

        return $result;
    }
    public static function isSerialized($data, $strict = true) {
        // If it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' === $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }
            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $data[0];
        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
                // Or else fall through.
            case 'a':
            case 'O':
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
        }
        return false;
    }

    public function isBase64($s){
        if ((bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s) === false) {
            return false;
        }
        $decoded = base64_decode($s, true);
        if ($decoded === false) {
            return false;
        }
        $encoding = mb_detect_encoding($decoded);
        if (! in_array($encoding, ['UTF-8', 'ASCII'], true)) {
            return false;
        }
        return $decoded !== false && base64_encode($decoded) === $s;
    }

    public function template($template_path)
    {
        $template_path = explode("/", $template_path);
        $template_group =  $template_path[0];
        $template_name = $template_path[1];

        $template_group_object = ee('Model')->get('TemplateGroup')->filter('group_name', $template_group)->first();
        if (empty($template_group_object)) return false;
        $templates = $template_group_object->Templates;
        $template_object = $templates->filter('template_name', $template_name)->first();
        if (empty($template_object)) return false;
        if (empty($template_object->template_data)) return false;
        
        return $template_object->template_data;
    }
    public static function parse($tagdata, $variables = [])
    {
        $oldTMPL = null;

        if (isset(ee()->TMPL)) {
            $oldTMPL = ee()->TMPL;
            ee()->remove('TMPL');
        }
        
        ee()->load->library('template', false, 'TMPL');
        $tagdata = ee()->TMPL->parse_variables($tagdata, $variables);
        ee()->TMPL->parse($tagdata);
        // Parse Global Variables
        $tagdata = ee()->TMPL->parse_globals($tagdata);
        // Remove comments
        $tagdata = ee()->TMPL->remove_ee_comments($tagdata);
        // Remove frontend edit link
        $tagdata = ee('pro:FrontEdit')->clearFrontEdit($tagdata);

        if ($oldTMPL !== null) {
            ee()->remove('TMPL');
            ee()->set('TMPL', $oldTMPL);
        }
        return $tagdata;
    }

    public static function toShortName(string $string) {
        // Replace spaces and non-alphanumeric characters with underscores.
        $shortName = preg_replace('/[^a-zA-Z0-9]+/', '_', $string);
    
        // Add underscores before capitalized letters in the middle.
        $shortName = preg_replace_callback('/([a-z])([A-Z])/', function ($matches) {
            return $matches[1] . '_' . $matches[2];
        }, $shortName);
    
        // Convert the string to lowercase.
        $shortName = strtolower($shortName);
    
        // Remove underscores from the beginning and end.
        $shortName = trim($shortName, '_');
    
        return $shortName;
    }   

    
    public static function action($method, $uri= '/') {
        // Check wether the action url is already set
        if (isset(ee()->session) && ee()->session->cache('{{addonName}}', $method)){
            $action_url = ee()->session->cache('{{addonName}}', $method);
        } else {
            // Remove namespance if its there:
            if (strpos($method, '\\') !== false) {
                $method = substr($method, strrpos($method, '\\') + 1);
            }
            $action_id = ee('Model')
                    ->get('Action')
                    ->filter('class', '{{addonName}}')
                    ->filter('method', $method)
                    ->first()
                    ->action_id;
            if (empty($action_id)) {
                return FALSE;
            }
            $action_url = $uri.QUERY_MARKER . 'ACT=' . $action_id;
            if (isset(ee()->session)) {
                ee()->session->set_cache('{{addonName}}', $method, $action_url);
            }
        }
        return $action_url;
    }

    public function getFileInfoFromURL($url) {
        $filename = basename($url);
        $file = ee('Model')->get('File')->filter('file_name', $filename)->first();
        if ($file) {
            $filedata = [
                'url' => $url,
                'path' => $file->getAbsolutePath(),
                'title' => $file->title,
                'file_size' => $file->file_size,
                'mime_type' => $file->mime_type,
            ];
        }
        return $filedata ?? false;
    }
    /**
     * Converts an array to an ExpressionEngine Grid-compatible format.
     *
     * @param array $data The array data to be converted into grid format.
     * @return array The formatted grid-compatible array.
     */
    public function toGrid(array $data, $level = 0): array
    {
        $gridArray = [];

        // Check if the input is an array
        if (is_array($data)) {
            // Loop through the data and create grid-compatible format
            foreach ($data as $key => $rowData) {
                if (is_object($rowData)) {
                    // Recursively mash keys if the value is an object
                    $rowData = $this->toGrid((array) $rowData, $level + 1);
                }
                if ($level > 0) {
                    // Add each item with a unique row_id and row_data
                    $gridArray[] = [
                        'subcount' => (int) $key + 1, // Ensure row_id starts at 1
                        'subtotal_results' => count($data),
                        'sublabel' => $key,
                        'subvalue' => $rowData,
                    ];
                } else {
                    // Add each item with a unique row_id and row_data
                    $gridArray[] = [
                        'count' => (int) $key + 1, // Ensure row_id starts at 1
                        'total_results' => count($data),
                        'label' => $key,
                        'value' => $rowData,
                    ];
                }
            }
        }

        return $gridArray;
    }
    /**
     * Recursively combines keys in a multi-dimensional array into a single string
     * with each level of keys joined by a colon.
     *
     * @param array $array The nested array to process.
     * @param string $prefix The key prefix used for recursion (initially empty).
     * @return array The processed array with combined keys.
     */
    public function keyMash(array $array, string $delimiter = '_', string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Combine the prefix and current key with a colon
            $newKey = $prefix ? $prefix . $delimiter . $key : $key;

            if (is_array($value) || is_object($value)) {
                // Recursively mash keys if the value is an array
                $result = array_merge($result, $this->keyMash((array)$value, $delimiter, $newKey));
            } else {
                // Otherwise, add the mashed key-value pair to the result
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Recursively convert an object to an array
     *
     * @param mixed $data The data to convert
     * @return array
     */
    public function toArray($data)
    {
        // If it's an object, convert it to an array
        if (is_object($data)) {
            $data = (array) $data;
        }

        // If it's an array, apply the function recursively to each element
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = $this->toArray($value);
            }
        }

        return $data;
    }

    
    public function findParams($find, $tagparams, $stripKey = true) {
        $params = [];

        foreach ($tagparams as $key => $value) {
            if (strpos($key, $find) === 0) {
                $paramKey = $stripKey ? substr($key, strlen($find)) : $key;
                $params[$paramKey] = $value;
            }
        }

        return $params;
    }

    function isNested(array $array): bool
    {
        // Loop through each element of the array
        foreach ($array as $value) {
            // Check if the value is an array
            if (is_array($value)) {
                // If it's an array, check if it's indexed (numeric keys)
                if (array_values($value) !== $value) {
                    // If the array keys are not numeric, it's not an indexed array
                    return false;
                }
            } else {
                // If the value is not an array, continue checking other elements
                continue;
            }
        }
        
        // If all nested arrays are indexed, return true
        return true;
    }
/**
 * Format the given data (array or object) into variables or variable pairs for ExpressionEngine templates.
 *
 * Single variables are returned as `'key' => 'value'`, while pairs are formatted as:
 * `'key' => [['key:subkey' => 'value']]`.
 *
 * @param mixed $variables The input data to format (array or object).
 * @param string $prefix Prefix to prepend to named keys (e.g., for nested keys).
 * @param callable|null $callback Optional callback function for custom actions.
 * @return array The formatted array for ExpressionEngine.
 */
public function variables($variables, $prefix = '', callable $callback = null): array
{
    $result = [];

    // Convert objects to arrays
    if (is_object($variables)) {
        $variables = (array) $variables;
    }

    // If not an array, return an empty result
    if (!is_array($variables)) {
        return [];
    }

    foreach ($variables as $key => $value) {
        // Detect if the key is named (string) or numeric
        $isNamedKey = is_string($key);

        // Only apply the prefix to named keys
        $newKey = $isNamedKey ? $prefix . $key : $key;

        // Apply the callback function if provided
        if ($callback) {
            $callbackResult = $callback($newKey, $value);
            if ($callbackResult !== null) {
                if (is_array($callbackResult)) {
                    foreach ($callbackResult as $newKeyCallback => $newValue) {
                        $result[$newKeyCallback] = $newValue;
                    }
                    continue;
                } else {
                    $value = $callbackResult;
                }
            }
        }

        // Recursively process nested arrays/objects
        if (is_array($value) || is_object($value)) {
            $nestedResult = $this->variables($value, $isNamedKey ? $newKey . ':' : $prefix, $callback);

            // Add the nested result directly for numeric keys; wrap in an array for named keys
            if ($isNamedKey) {
                $result[$newKey] = [$nestedResult];
            } else {
                $result[$key] = $nestedResult;
            }
        } else {
            // Add the value directly for numeric keys or with the prefixed key for named keys
            if ($isNamedKey) {
                $result[$newKey] = $value;
            } else {
                $result[$key] = $value;
            }
        }
    }

    return $result;
}



}

// EOF
