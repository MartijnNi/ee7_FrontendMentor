<?php
// @codingStandardsIgnoreFile

use ExpressionEngine\Service\Migration\Migration;

class Create{{migration_class}}PostAction extends Migration
{
    /**
     * Execute the migration
     * @return void
     */
    public function up()
    {
        ee('Model')->make('Action', [
            'class' => '{{class}}',
            'method' => 'Post',
            'csrf_exempt' => true,
        ])->save();
    }

    /**
     * Rollback the migration
     * @return void
     */
    public function down()
    {
        ee('Model')->get('Action')
            ->filter('class', '{{class}}')
            ->filter('method', 'Post')
            ->delete();
    }
}
