<?php

require_once PATH_THIRD.'{{addonName}}/Core/Traits/AddonConfig.php';
use {{namespace}}\Core\Traits\AddonConfig;
use {{namespace}}\Service\Helpers;

return (new class {
    use AddonConfig;
    public function setup()
    {
        $config = $this->config()->setup;
        // Additional configuration
        $config->services = [
            'Helper' => fn ($addon) => new Helpers(),
        ];
        $config->models = [
            'Settings'    => 'Model\Settings',
        ];
        return (array) $config;
    }
})->setup();