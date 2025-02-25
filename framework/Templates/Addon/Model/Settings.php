<?php

namespace {{namespace}}\Model;

use {{namespace}}\Core\Traits\AddonConfig;
use ExpressionEngine\Service\Model\Model;

class Settings extends Model
{
    // Documentation: https://docs.expressionengine.com/latest/development/services/model/building-your-own.html
    // You can get this all instances of this model by using:
    // ee('Model')->get('testaddon:Settings')->all();
    use AddonConfig;

    protected static $_primary_key = 'settings_id';
    protected static $_table_name = 'exp_{{addonName}}_settings';
    protected $settings_id;
    protected $site_id;
    protected $fieldtype;
    protected $key;
    protected $value;
    protected $options;
    protected static $_validation_rules = array(
        'fieldtype' => 'enum[action_button, checkbox, dropdown, file, file_picker, grid, hidden, html, multiselect, password, radio, select, short_text, slider, table, text, textarea, toggle, yes_no]',
        'key' => 'unique',
    );
    protected static $_events = ['beforeInsert'];
    // Example of a property getter
    public function onBeforeInsert()
    {
        $this->setRawProperty('site_id', ee()->config->item('site_id'));
    }
    
    protected function get__value()
    {
        $value = $this->value;

        if (!empty($value) && ee('{{addonName}}:Helper')->isSerialized($value)) {
            $value = @unserialize($value);
        }

        return $value;
    }

    protected function set__value($value)
    {

        if (is_array($value) && !empty($value)) {
            $value = serialize($value);
        }

        $this->setRawProperty('value', $value);
    }

    protected function get__options()
    {
        $value = $this->options;

        // Decode base64 if needed
        $value = $this->decodeBase64($value);

        if (!empty($value) && ee('{{addonName}}:Helper')->isSerialized($value)) {
            $value = @unserialize($value);
        }

        return $value;
    }

    protected function set__options($value)
    {
        if (is_array($value) && !empty($value)) {
            $value = serialize($value);
        }

        // Encode the options value to base64
        $value = $this->encodeBase64($value);

        $this->setRawProperty('options', $value);
    }


    /**
     * Decode base64 encoded data.
     *
     * @param string $data
     * @return mixed
     */
    private function decodeBase64($data)
    {
        if (is_string($data)) {
            $decoded = base64_decode($data, true); // Strict decoding

            // Only return decoded data if valid
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $data; // Return original if decoding fails
    }

    /**
     * Encode data to base64.
     *
     * @param mixed $data
     * @return string
     */
    private function encodeBase64($data)
    {
        if (is_string($data) || is_array($data)) {
            return base64_encode($data);
        }

        return $data; // Return original data if not encodable
    }
}
