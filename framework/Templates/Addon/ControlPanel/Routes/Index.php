<?php

namespace {{namespace}}\ControlPanel\Routes;

use {{namespace}}\Core\Traits\AddonConfig;
use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;

class Index extends AbstractRoute
{
    use AddonConfig;
    /**
     * @var string
     */
    protected $route_path = '';

    /**
     * @var string
     */
    protected $cp_page_title = 'Index';

    /**
     * @param false $id
     * @return AbstractRoute
     */
    public function process($id = false)
    {
        $this->addBreadcrumb('', 'Index');

        // Use default options
        $table = ee('CP/Table');

        // Specify other options
        $table = ee('CP/Table', array(
            'autosort' => TRUE, 
            'autosearch' => TRUE,
            'reorder' => TRUE,
            'sortable' => FALSE
        ));


        
        $table->setColumns(
            array(
                'setting',
                'value'
                )
            );
            
        $table->setNoResultsText('no_settings');

        //  settings the data
        $settings = ee('Model')->get('{{addonName}}:Settings')->fields('key', 'value')->all();
        
        $tableData = [];
        
        foreach ($settings as $setting) {
            $tableData[] = [
                'key' => $setting->key,
                'value' => is_array($setting->value) 
                    ? json_encode($setting->value) : $setting->value
                ];
        }
        
        $table->setData($tableData);

        ee()->cp->add_js_script('file', 'cp/sort_helper');
        ee()->cp->add_js_script('plugin', 'ee_table_reorder');
        ee()->cp->add_to_foot("  <script>
            $('table').eeTableReorder({
                afterSort: function(row) {
                    // Whatever you like
                }
            });
            </script>
        ");
            
        $variables = [
            'table' => $table->viewData(ee('CP/URL', 'addons/settings/{{addonName}}'))
        ];

        $this->setBody('Index', $variables);

        return $this;
    }
}
