<?php

namespace AVB\DevelopmentFramework\Core;

class TemplateParser
{
    /**
     * Recursively copies templates and replaces placeholders in file names and content.
     *
     * @param string $templateDir Path to the template directory.
     * @param string $targetDir Path to the target directory where templates will be copied.
     * @param array $placeholders Associative array of placeholders and their values.
     * @throws \Exception
     */
    public function parseTemplates(string $templateDir, string $targetDir, array $placeholders): void
    {
        if (!is_dir($templateDir)) {
            throw new \Exception("Template directory does not exist: {$templateDir}");
        }

        // Recursively iterate through the template directory
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($templateDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($templateDir, '', $item->getPathname());
            $relativePath = $this->replacePlaceholders($relativePath, $placeholders);
            $destinationPath = $targetDir . $relativePath;

            if ($item->isDir()) {
                // Create directories if not exist
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
            } else {
                // Ensure the directory exists before writing the file
                $this->ensureDirectoryExists(dirname($destinationPath));

                // Replace placeholders in file content and copy
                $content = file_get_contents($item->getPathname());
                $content = $this->replacePlaceholders($content, $placeholders);
                file_put_contents($destinationPath, $content);
            }
        }
    }

    /**
     * Ensures that the directory exists before writing a file.
     *
     * @param string $dir The directory to check/create.
     * @throws \Exception If the directory cannot be created.
     */
    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \Exception("Failed to create directory: {$dir}");
            }
        }
    }

    /**
     * Replaces placeholders in a given string.
     *
     * @param string $content Content where placeholders will be replaced.
     * @param array $placeholders Associative array of placeholders and their values.
     * @return string The content with placeholders replaced.
     */
    private function replacePlaceholders(string $content, array $placeholders): string
    {
        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace("{{{$placeholder}}}", $value, $content);
        }
        return $content;
    }
}
