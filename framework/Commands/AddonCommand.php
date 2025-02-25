<?php

namespace AVB\DevelopmentFramework\Commands;

use AVB\DevelopmentFramework\Core\TemplateParser;

class AddonCommand extends Command
{
    protected $command = 'make:addon';
    protected $description = 'Creates a new addon in the addons folder.';

    public function execute(array $args): void
    {
        // Validate addon name
        if (empty($args['name'])) {
            echo "Error: Addon name is required.\n";
            return;
        }

        $author = 'Antwan van Boheemen';

        // Get the raw addon name and format it
        $addonName = strtolower($this->formatName($args['name'], '_'));
  
        // Get the raw addon namespace and format it
        $addonNamespace = $this->formatName($author.'\\ '.$args['name']);
        
        // Define the folder path for the new addon
        $addonFolder = DEFAULT_PROJECT_PATH . "/website/system/user/addons/{$addonName}";

        // Check if addon already exists
        if (is_dir($addonFolder)) {
            echo "Error: Addon '{$addonName}' already exists.\n";
            return;
        }

        // Define placeholders
        $placeholders = [
            'name'        => $args['name'],
            'addonName'   => $addonName,
            'namespace'   => $addonNamespace,
            'class'       => ucfirst($this->formatName($args['name'], '_')),
            'migration_class' => $this->formatName($args['name']),
            'author'      => $author,
            'authorUrl'   => '',
            'description' => '',
            'version'     => '1.0.0',
        ];

        // Template directory path
        $templateDir = __DIR__ . '/../Templates/Addon';

        try {
            // Use the TemplateParser to parse and copy templates
            $parser = new TemplateParser();
            $parser->parseTemplates($templateDir, $addonFolder, $placeholders);

            echo "Addon '{$addonName}' created successfully in {$addonFolder}.\n";
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
        }
    }

    /**
     * Formats the addon name into a proper namespace format.
     * 
     * @param string $addonName The raw addon name (e.g., "Test addon 2").
     * @return string The formatted namespace (e.g., "TestAddon2").
     */
    private function formatName(string $name, string $delimter = ''): string
    {
        // Remove extra spaces, split by spaces, and capitalize each word.
        $words = explode(' ', strtolower(trim($name)));

        foreach ($words as $k => $word) {
            $words[$k] = ucfirst($word);
        }
        
        
        return implode($delimter, $words);
    }

}
