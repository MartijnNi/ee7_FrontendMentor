<?php

namespace AVB\DevelopmentFramework\Commands;

class DownloadCommand extends Command
{
    protected $command = 'download';

    protected $description = 'Downloads and organizes the files and folders to the recommended setup';
    public function execute(array $args): void
    {
        $webRoot = DEFAULT_PROJECT_PATH.'/website';
        // Define the anonymous function to prompt for version input
        $version = $_ENV['EE_VERSION'] ?? '';

        if (empty($version)) {
            $version = fn($default = ''): string => $default ?: trim(fgets(STDIN));
        }    
        // Check if the version is valid
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $this->logger->log("Please specify a valid EE version", 'error');
            exit;
        }

        $targetDirectory = '/var/www/html/website';
        $publicDirectory = $targetDirectory . '/public_html';
        $systemDirectory = $targetDirectory . '/system';
        $tempDirectory = '/var/www/html/website/temp';
        $fileName = "ExpressionEngine{$version}.zip";
        $url = "https://github.com/ExpressionEngine/ExpressionEngine/releases/download/{$version}/{$fileName}";
        $zipFilePath = "{$tempDirectory}/{$fileName}";
        $commands = [];
        if (!is_dir($webRoot . "/public_html")) {
            $commands[] = "mkdir $webRoot/public_html";
        } elseif(is_dir($webRoot . "/public_html") && is_dir($webRoot . "/system")) {
            $time = time();
            $commands = [
                (!is_dir(DEFAULT_PROJECT_PATH . "/.local")) ? "mkdir " . DEFAULT_PROJECT_PATH . "/.local": "ls",
                "mv ".$webRoot . "/system ".DEFAULT_PROJECT_PATH . "/.local/system_backup_{$time}",
                "mv ".$webRoot . "/public_html ".DEFAULT_PROJECT_PATH . "/.local/public_backup_{$time}",
                "mkdir " . $webRoot . "/public_html",  
            ];
        }
        $response = $this->runLocal($commands);
        if ($response['code'] !== 0) {
            $this->logger->log($response['message'], 'error');
            exit;
        }
        
        $this->logger->log("Downloading Expression Engine Version $version",'info');
       
        $commands = [
            "echo 'Downloading Expression Engine {$version}'",
            "mkdir {$tempDirectory}",
            "curl -L --output {$zipFilePath} {$url}",
            "unzip {$zipFilePath} -d {$tempDirectory}",
            "mv {$tempDirectory}/system {$systemDirectory}",
            "mkdir -p {$publicDirectory}",
            "mv {$tempDirectory}/* {$publicDirectory}/",
            "rm -rf {$tempDirectory} {$zipFilePath}",
            "rm {$publicDirectory}/{$fileName}",
            "sed -i \"s|'./system'|'../system'|g\" {$publicDirectory}/admin.php",
            "sed -i \"s|'./system'|'../system'|g\" {$publicDirectory}/index.php",
            "mv {$publicDirectory}/admin.php {$publicDirectory}/cms.php",
        ];


        $response = $this->runWebserver($commands);
        if ($response['code'] !== 0) {
            print_r($response);
            // $this->logger->log($response['message'], 'error');
            exit;
        }

        $configTmplFilePath = $webRoot . "/system/ee/installer/config/config_tmpl.php";
        $configTmplFileHandle = fopen($configTmplFilePath, 'r');
        if ($configTmplFileHandle) {
            $configTmplFileContents = fread($configTmplFileHandle, filesize($configTmplFilePath));
            fclose($configTmplFileHandle);

            $configTmplFileContents = str_replace(
                "{extra_config}",
                "{extra_config}\n\ninclude_once SYSPATH.'/../config.php';\n",
                $configTmplFileContents
            );

            $configTmplFileHandle = fopen($configTmplFilePath, 'w');
            if ($configTmplFileHandle) {
                fwrite($configTmplFileHandle, $configTmplFileContents);
                fclose($configTmplFileHandle);
                $this->logger->log("Downloaded and Organized Expression Engine Version $version",'success');
            } else {
                $this->logger->log("Error opening config file for writing.",'error');
                exit;
            }
        } else {
            $this->logger->log("Error opening config file for reading.",'error');
            exit;
        }
    }
}
