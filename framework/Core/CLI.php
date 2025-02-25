<?php

namespace AVB\DevelopmentFramework\Core;

use AVB\DevelopmentFramework\Core\Logger;

class CLI {
    protected $command;
    protected $commands;
    protected $logger;

    protected $args = [];
    public function __construct() {
        $this->logger = new Logger();
        // Parse command line arguments
        $this->parse();
        $this->loadCommands();
        $this->handleCommand();
    }

    private function parse(): void {
        global $argv;
    
        // Skip the first argument (script name)
        $args = array_slice($argv, 1);
    
        // Extract the command (first argument)
        $this->command = array_shift($args);
    
        // Initialize the $args array
        $this->args = [];
    
        // Parse arguments and flags
        $count = count($args);
        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];
            if (strpos($arg, '--') === 0) {
                // Flag
                $flag = substr($arg, 2); // Remove leading '--'
                // Check if the flag has a corresponding value
                if ($i + 1 < $count && strpos($args[$i + 1], '--') !== 0) {
                    $value = $args[$i + 1];
                    $this->args[$flag] = $value; // Set flag-value pair
                    $i++; // Skip the next argument
                } else {
                    $this->args[$flag] = true; // Set flag-value pair
                    $i++; // Skip the next argument
                }
            } else {
                // Standalone value (not associated with any flag)
                // Skip standalone values
                $this->logger->log("Standalone value '{$arg}' not associated with any flag", 'error');
            }
        }
    }

    protected function loadCommands(): void {
        $commandsDir = __DIR__ . '/../Commands';
        $files = glob($commandsDir . '/*.php');
        
        foreach ($files as $file) {
            $className = basename($file, '.php');
            // Dynamically include the file
            if ($className == 'Command') continue;
            require_once $file;
            // Check if the class exists
            if (class_exists("AVB\\DevelopmentFramework\\Commands\\$className")) {
                $fullClassName = "AVB\\DevelopmentFramework\\Commands\\$className";
                $commandInstance = new $fullClassName();
                // Add command to the list of available commands
                $this->commands[$commandInstance->getName()] = [
                    'description' => $commandInstance->getDescription(),
                    'className' => $fullClassName,
                ];
            } else {
                $this->logger->log("Class $className not found in file $file", 'error');
            }
        }
    }

    protected function handleCommand(): void {
        if (isset($this->commands[$this->command])) {
            $commandData = $this->commands[$this->command];
            $commandInstance = new $commandData['className']();
            $commandInstance->execute($this->args);
        } else {
            $this->logger->log("Command '{$this->command}' not found", 'error');
        }
    }

    public function getArgs(): array {
        return $this->args;
    }

    public function getCommand(): ?string {
        return $this->command;
    }
}