<?php

namespace {{namespace}}\ControlPanel\Routes;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;
use {{namespace}}\Core\Traits\CP\HasSharedForm;

class Settings extends AbstractRoute
{
    use HasSharedForm;
    /**
     * @var string
     */
    protected $route_path = 'settings';

    /**
     * @var string
     */
    protected $cp_page_title = 'Settings';

    /**
     * @param false $id
     * @return AbstractRoute
     */
    public function process($id = false)
    {
        $method = end(ee()->uri->segments);

        switch ($method) {
            case 'update':
                return $this->update();
            case $this->route_path;
                return $this->index();
            default:
                show_404();
        }
    }

    public function index() {
   
          // set the breadcrumb
          $this->addBreadcrumb('settings', 'Settings');
          // call our getForm() method to get
          // our array
          $data = ee('Model')->get($this->getAddonName().':Settings')->all();
          $fields = [];

          foreach ($data as $key => $setting) {
            $fields['group_name'][$key] = [
                'name' => $setting->key,
                'value'=> $setting->value,
                'type' => $setting->fieldtype,
                'settings' => $setting->options
            ];
          }

          $action = ee('CP/URL','addons/settings/{{addonName}}/settings/update');
          $form = $this->makeForm($action, $fields);
  
          // store our form in our $variables array
          // to be passed into our view
          $variables = [
              'form'  => $form
          ];
  
          $this->setBody('Settings', $variables);
  
          return $this;
    }

    public function update() {
        $settings = ee('Model')->get($this->getAddonName().':Settings')->all();
        $errors = [];
        foreach ($settings as $setting) {
            if ($setting->fieldtype == 'table') continue;
            
            $rules = $setting->options->rules ?? [];
            $result = ee('Validation')->make($rules)->validate($_POST);
            $value = ee()->input->post($setting->key);

            if (is_array($value) && $setting->fieldtype == 'grid') {
                $gridData = [];
                $gridRows = $value;
                foreach ($gridRows as $row) {
                    foreach ($row as $cell) {
                        $gridData[] = $cell;
                    }
                }
                $value = $gridData;
            }


            if ($result->isValid()) {
                $setting->value = $value;
                $setting->save();
            } else {
                $errors = $result->getAllErrors();
            }
        }
        
        if (empty($errors)){
            ee('CP/Alert')->makeInline('settings-save')
            ->asSuccess()
            ->withTitle(lang('Settings saved!'))
            ->addToBody(lang('Saved the settings successfully'))
            ->defer();
        } else {
            $messages = '';
            foreach ($errors as $field => $field_errors) {
                foreach ($field_errors as $message) {
                    $messages .= "<strong>".lang($field).'</strong> - '.$message.BR;
                }
            }
            ee('CP/Alert')->makeInline('settings-save')
            ->asIssue()
            ->withTitle(lang('Oops! Something went wrong'))
            ->addToBody($messages)
            ->defer();
        }
        
        redirect( ee('CP/URL', '/cp/addons/settings/{{addonName}}/settings'));
    }
    
    
}
