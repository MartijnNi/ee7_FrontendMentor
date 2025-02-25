<?php
/**
 * This file contains helper functions to simplify development
 * 
 */

namespace {{namespace}}\Core\Traits\CP;

use ExpressionEngine\Addons\Rte\RteHelper;

/**
 * Add-on Service
 */
trait HasSharedForm
{
    protected function makeForm($action, $formFields = [])
    {
        $form = ee('CP/Form');
        $form->asTab();
        $form->asFileUpload();
        $form->setBaseUrl($action);

        foreach ($formFields as $group => $fields) {
            $fieldGroup = $form->getGroup($group);

            foreach ($fields as $field) {
                if ($field['type'] == 'hidden') {
                    $formField = $form->getHiddenField($field['name'], $field['type']);
                } else {
                    $fieldSet = $fieldGroup->getFieldSet($field['name']);
                    if ($field['type'] == 'grid') {
                        $fieldSet->withGrid();
                    }
                    if ($field['type'] == 'textarea') {
                        $formField = $fieldSet->getField($field['name'], 'html');
                    } else {
                        $formField = $fieldSet->getField($field['name'], $field['type']);
                    }
                }
                $this->configureField($formField, $field);
            }
        }

        return $form->toArray();
    }
    

    protected function configureField($formField, $field)
    {
        switch ($field['type']) {
            case 'action_button':
                $this->configureActionButton($formField, $field);
                break;
            case 'dropdown':
            case 'radio':
            case 'select':
            case 'checkbox':
                $this->configureChoicesField($formField, $field);
                break;
            case 'multiselect':
                $this->configureMultiselectField($formField, $field);
                break;
            case 'grid':
                $this->configureGridField($formField, $field);
                break;
            case 'table':
                $this->configureTableField($formField, $field);
                break;
            case 'textarea':
                $this->test($formField, $field);
            case 'slider':
                $this->configureSliderField($formField, $field);
                break;
            case 'html':
                $this->configureHTMLField($formField, $field);
                break;                
            case 'hidden':
            default:
                $formField->setValue($field['value']);
                break;
        }
    }

    protected function test($formField, $field) {
        if (ee('Addon')->get('rte')->isInstalled()) {
           $field = $this->handleRteField($field);
        }

        return $formField->setContent(form_textarea($field));
    }

    protected function configureActionButton($formField, $field)
    {
        $formField->setValue($field['value'])
            ->setLink(ee('CP/URL', $field['settings']['url']))
            ->setText($field['settings']['label']);
    }

    protected function configureChoicesField($formField, $field)
    {
        $choices = $field['settings']['choices'] ?? [];
        $formField->setValue($field['value'])
            ->setChoices($choices);
    }

    protected function configureMultiselectField($formField, $field)
    {
        $dropdowns = $field['settings']['choices'] ?? [];
        foreach ($dropdowns as $dropdown) {
            $dropdownChoices = ['' => lang('== Make a choice == ')] + $dropdown['choices'];
            $formField->addDropdown(
                "{$field['name']}[{$dropdown['key']}]",
                $field['value'][$dropdown['key']] ?? '',
                $dropdown['label'],
                $dropdownChoices
            )->setDisabled('');
        }
    }

    protected function configureGridField($formField, $field)
    {
        $grid = $formField->setOptions([
            'field_name' => $formField->getName(),
            // 'reorder'    => true,
        ]);

        $columns = $field['settings']['columns'];

        if (empty($columns))
            return;

        $grid->setColumns($columns);

        $data = $field['value'] ? $field['value'] : [];

        $grid->setColumns($columns);

        $colData = [];
        foreach (array_keys($columns) as $column) {
            $colData[] = $field['settings'][$column];
        }
        $grid->defineRow($colData);

        if ($data) {
            $grid->setData($data);
        }

        $grid->setBaseUrl(ee('CP/URL')->make($this->base_url));
    }
    protected function configureTableField($formField, $field)
    {
        $table = $formField->setOptions([
            'lang_cols' => true,
            'class' => $field['settings']['class'] ?? '',
        ]);

        $columns = $field['settings']['columns'];

        if (empty($columns))
            return;

        $table->setColumns($columns);

        $data = $field['value'] ? $field['value'] : [];

        $table->setColumns($columns);

    
        if ($data) {
            $table->setData($data);
        }

        $table->setBaseUrl(ee('CP/URL')->make($this->base_url));
    }

    protected function configureHTMLField($formField, $field) {
        // Clean the input using ExpressionEngine's XSS cleaner
        $cleanedValue = ee('Security/XSS')->clean($field['value']);
        
        // Remove all occurrences of "[removed]" including the content between the markers
        $cleanedValue = preg_replace('/\[removed\].*?\[removed\]/is', '', $cleanedValue);
    
        // Add a hidden field with the sanitized value
        $hiddenField = form_hidden($field['name'], $cleanedValue);
    
        // Set the sanitized content to the form field
        $formField->setContent($hiddenField . $cleanedValue);
    }

    protected function configureSliderField($formField, $field) {
        $settings = $field['settings'];	
        // setMin($min)
        if (isset($settings['min'])) {
            $formField->setMin($settings['min']);
        }
        // setMax($max)
        if (isset($settings['max'])) {
            $formField->setMax($settings['max']);
        }
        // setStep($step)
        if (isset($settings['step'])) {
            $formField->setStep($settings['step']);
        }
        // setUnit($unit)
        if (isset($settings['unit'])) {
            $formField->setUnit($settings['unit']);
        }
        $formField->setValue($field['value']);
    }

    private function handleRteField($field) {
         // Get the first toolset available
         $toolset = ee('Model')->get('rte:Toolset')->first();
         // Load proper toolset
         $serviceName = ucfirst($toolset->toolset_type) . 'Service';
         $configHandle = ee('rte:' . $serviceName)->init($toolset->settings, $toolset);
         $id = $field['name'];
         $defer = false;

         ee()->cp->add_to_foot('<script type="text/javascript">new Rte("' . $id . '", "' . $configHandle . '", ' . ($defer ? 'true' : 'false') . ');</script>');

         $data = $field['value'];
         
         // convert file tags to URLs
         RteHelper::replaceFileTags($data);

         // convert site page tags to URLs
         RteHelper::replacePageTags($data);

         //Third party conversion
         RteHelper::replaceExtraTags($data);

         $field = array(
             'name' => $field['name'],
             'value' => $data,
             'id' => $id,
             'data-config' => $configHandle,
             'class' => ee('rte:' . $serviceName)->getClass(),
             'data-defer' => ($defer ? 'y' : 'n')
         );
         return $field;
    }
}