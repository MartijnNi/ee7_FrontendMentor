<?php
/**
 * This file contains helper functions to simplify development
 * 
 */

namespace {{namespace}}\Core\Traits;

use {{namespace}}\Service\Helpers;

/**
 * Add-on Service
 */
trait AddonConfig
{
  private $addonConfig;
  private function _initializeAddonConfig() {
    $this->addonConfig = new \stdClass;
    $this->addonConfig->setup = (object) [
      'name'              => '{{name}}',
      'short_name'        => '{{addonName}}',
      'description'       => '{{description}}',
      'version'           => '{{version}}',
      'author'            => '{{author}}',
      'author_url'        => '{{authorUrl}}',
      'namespace'         => '{{namespace}}',
      'settings_exist'    => true,
    ];
    $this->addonConfig->tables = (object)
      [
        'settings' => (object) [
          'name' => '{{addonName}}_settings',
          'model' => '{{addonName}}:Settings',
          'fields' => [
                'settings_id' => ['type' => 'int', 'constraint' => '10', 'unsigned' => true, 'auto_increment' => true],
                'site_id' => ['type' => 'int', 'constraint' => '4', 'unsigned' => true, 'default' => 1],
                'fieldtype' => ['type' => 'varchar', 'constraint' => '100'],
                'key' => ['type' => 'varchar', 'constraint' => '100', 'unique' => true],
                'value' => ['type' => 'text'],
                'options' => ['type' => 'text']
          ],
          'data' => [
              ['site_id' => 1, 'fieldtype' => 'action_button', 'key' => 'submit_action', 'value' => 'Submit', 'options' => ['url' =>'http://localhost/', 'label' => 'Go to homepage']],
              ['site_id' => 1, 'fieldtype' => 'checkbox', 'key' => 'newsletter_opt_in', 'value' => ['yes'], 'options' => ['rules' => ['required'], 'choices' => ['yes' => 'Yes', 'no' => 'No']]],
              ['site_id' => 1, 'fieldtype' => 'dropdown', 'key' => 'country', 'value' => ['Netherlands'], 'options' => ['choices' => ['Netherlands', 'Belgium', 'Germany']]],
              ['site_id' => 1, 'fieldtype' => 'file', 'key' => 'profile_picture', 'value' => ''],
              ['site_id' => 1, 'fieldtype' => 'file_picker', 'key' => 'resume', 'value' => ''],
              ['site_id' => 1, 'fieldtype' => 'grid', 'key' => 'work_experience', 'value' => [['company' => 'Dunique', 'position' => 'Web Developer'], ['company' => 'Freelance', 'position' => 'Designer']], 'options' => [
                'columns' => [
                  'company' => ['sort' => false],
                  'position' => ['sort' => false]
                ],
                'company' =>  ['name' => 'company', 'type' => 'text', 'value' => ''],
                'position' => ['name' => 'position', 'type' => 'select', 'value' => '', 'choices' => ['Developer' => 'Developer', 'Designer' => 'Designer']]
              ]],
              ['site_id' => 1, 'fieldtype' => 'hidden', 'key' => 'user_id', 'value' => 12345],
              ['site_id' => 1, 'fieldtype' => 'html', 'key' => 'terms', 'value' => '<p>Terms and Conditions</p>'],
              [
                'site_id' => 1, 
                'fieldtype' => 'multiselect', 
                'key' => 'skills', 
                'value' => '', 
                'options' => 
                  ['choices' => [
                    [
                      'key' => 'frontend', 
                      'label' => 'Frontend Development',
                      'choices' => ['html'=>'HTML', 'css' => 'CSS', 'js' => 'JavaScript']
                    ],
                    [
                      'key' => 'backend', 
                      'label' => 'Backend Development',
                      'choices' => ['php' => 'PHP', 'python'=>'Python', 'nodejs' => 'Node.js']
                    ]
                  ]
                ]
              ],
              ['site_id' => 1, 'fieldtype' => 'password', 'key' => 'password', 'value' => 'secret_password'],
              ['site_id' => 1, 'fieldtype' => 'radio', 'key' => 'gender', 'value' => 'male', 'options' => ['choices' => ['male', 'female', 'other']]],
              ['site_id' => 1, 'fieldtype' => 'select', 'key' => 'language', 'value' => 'Dutch', 'options' => ['choices' => ['Dutch' => 'Dutch', 'English' => 'English', 'French' => 'French']]],
              ['site_id' => 1, 'fieldtype' => 'short-text', 'key' => 'nickname', 'value' => 'Antwan'],
              ['site_id' => 1, 'fieldtype' => 'slider', 'key' => 'experience', 'value' => 5, 'options' => ['min' => 1, 'max' => 10]],
              ['site_id' => 1, 'fieldtype' => 'table', 'key' => 'education', 'value' => [['degree' => 'BSc', 'year' => 2015], ['degree' => 'MSc', 'year' => 2018]], 'options' => [
                'columns' => [
                  'degree' => ['sort' => false],
                  'year' => ['sort' => false]
                ]
              ],
              ['site_id' => 1, 'fieldtype' => 'hidden', 'key' => 'user_id', 'value' => 12345],
              ['site_id' => 1, 'fieldtype' => 'html', 'key' => 'terms', 'value' => '<p>Terms and Conditions</p>']],
              ['site_id' => 1, 'fieldtype' => 'text', 'key' => 'website_name', 'value' => '{site_name}'],
              ['site_id' => 1, 'fieldtype' => 'textarea', 'key' => 'text_field', 'value' => 'It just works!'],
              ['site_id' => 1, 'fieldtype' => 'toggle', 'key' => 'newsletter', 'value' => 1],
              ['site_id' => 1, 'fieldtype' => 'yes_no', 'key' => 'agree_to_terms', 'value' => 'yes'],
            ]
        ]
    ];
  }
  public function config() {
    if (!$this->addonConfig) {
        $this->_initializeAddonConfig();
    }
    return $this->addonConfig;
  }

}