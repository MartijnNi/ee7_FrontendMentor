<?php

use {{namespace}}\Core\Traits\AddonConfig;
use ExpressionEngine\Service\Migration\Migration;

class Create{{migration_class}}SettingsTable extends Migration
{
    use AddonConfig;

    /**
     * Execute the migration
     * @return void
     */
    public function up()
    {        
        $config = $this->config();
        if ($config->setup->settings_exist) {
            $settings = $this->config()->tables->settings;
            $table = $settings->name;
            $fields = $settings->fields;

            ee()->dbforge->add_field($fields);
            ee()->dbforge->add_key(array_keys($this->config()->tables->settings->fields)[0], true);
            ee()->dbforge->create_table($table);

            if (isset($settings->data) && !empty($settings->data)) {
                if (isset($settings->model) && !empty($settings->model)) {
                    foreach ($settings->data as $record) {
                        ee('Model')->make($settings->model)->set($record)->save();
                    }
                } else {
                    ee()->db->insert_batch($table, $settings->data);
                }
            }
        }
    }

    /**
     * Rollback the migration
     * @return void
     */
    public function down()
    {
        $table = $this->config()->tables->settings->name;
        ee()->dbforge->drop_table($table);
    }
}
