<?php

namespace AVB\DevelopmentFramework\Commands;

use AVB\DevelopmentFramework\Core\TemplateParser;

class BoilerplateCommand extends Command
{
    protected $command = 'generate:boilerplate';
    protected $description = 'Sets up a default system/user/ configuration';

    public function execute(array $args): void
    {
        // // Validate addon name
        // if (empty($args['name'])) {
        //     echo "Error: Addon name is required.\n";
        //     return;
        // }

        
        // Define the folder path for the new addon
        $userFolder = DEFAULT_PROJECT_PATH . "/website/";

        // Check if addon already exists
        if (is_dir($userFolder)) {
            $continue = $this->promptYesNo('This will overwrite all files and folders, are you sure?');
            // print_r($continue);
            if ($continue != 1)  {
                $this->logger->log("Cancelled {$this->command} operation, user folder exists",'error');
                return;
            }
            // $continue = $this->promptYesNo('Do you have a backup?');
            // if ($continue != 1)  {
            //     $this->logger->log("Cancelled {$this->command} operation, user folder exists",'error');
            //     return;
            // }
            // $continue = $this->promptYesNo('Are you really, really, REALLY sure??'); // Read input from CLI
            // if ($continue != 1)  {
            //     $this->logger->log("Cancelled {$this->command} operation, user folder exists",'error');
            //     return;
            // }
            $this->logger->log("Ok, overwriting all files in {$userFolder}");
        }

        // Define placeholders
        $placeholders = $_ENV;

        // Template directory path
        $templateDir = __DIR__ . '/../Templates/Default';

        try {
            // Use the TemplateParser to parse and copy templates
            $parser = new TemplateParser();
            $parser->parseTemplates($templateDir, $userFolder, $placeholders);

            echo "Boilerplate added successfully in {$userFolder}.\n";
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
        }
    }

}
