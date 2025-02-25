<?php
namespace AVB\DevelopmentFramework\Commands;

use AVB\DevelopmentFramework\Core\Logger;

abstract class Command {
    protected $command;
    protected $description;
    protected $logger;
    
    protected $webserverName;
    protected $mysqlName;

    public function __construct() {
        $this->logger = new Logger();
        $this->webserverName = 'ddev-'.$_ENV['DDEV_SITENAME']. '-web';
        $this->mysqlName = 'ddev-'.$_ENV['DDEV_SITENAME']. '-db';
    }
    public function execute(array $args): void {}

    public function getName(): string {
        return $this->command;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function runLocal(array $commands): array {
        $command = implode(' && ', $commands);
        return $this->handleResponse($command);
    }

    public function runWebserver(array $commands, $user = 'www-data'): array {
        foreach ($commands as $key => $command) {
            $commands[$key] = $command;
        }

        $command = implode(' && ', $commands);
        return $this->handleResponse($command);
    }
    public function runMysql(string $command): array {        
        $command = "mysql " . $command;
        return $this->handleResponse($command);
    }

    private function handleResponse($command) {
        // Initialize variables to store the process output and error
        $output = '';
        $errorOutput = '';
        
        // Execute the command
        $process = proc_open($command, [
            ['pipe', 'r'], // stdin
            ['pipe', 'w'], // stdout
            ['pipe', 'w'], // stderr
        ], $pipes);

        // Check if the process was successfully created
        if (is_resource($process)) {
            // Read the stdout and stderr streams
            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            // Close all pipes
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            // Close the process
            $returnCode = proc_close($process);

            // Check if the return code indicates an error
            if ($returnCode !== 0) {
                // Return error code and message
                return ['code' => $returnCode, 'message' => $errorOutput];
            }
        } else {
            // Failed to create the process
            return ['code' => -1, 'message' => 'Failed to execute command'];
        }

        // Return output and success code
        return ['code' => 0, 'message' => $output];
    }
   
    protected function promptYesNo($question): bool  {
        while (true) {
            echo 'Answer [yes,no] - '.$question. ' ';
            $input = strtolower(trim(fgets(STDIN)));
            if ($input === 'yes') {
                return true;
            } else {
                return false;
            }
        }
    }
}