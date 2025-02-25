<?php
namespace AVB\DevelopmentFramework\Commands;

class ListCommand extends Command {
  protected $command = 'list';
  protected $description = 'Generates a list of available commands';

  protected $commands = [];

  public function execute(array $args): void {
      $this->loadCommands();
      $this->logger->table($this->commands);
  }
  protected function loadCommands(): void {
    $commandsDir = __DIR__ . '/../Commands';
    $files = glob($commandsDir . '/*.php');
    foreach ($files as $file) {
        $className = basename($file, '.php');
        if ($className == 'Command') continue;
        // Dynamically include the file
        require_once $file;
        // Check if the class exists
        if (class_exists("AVB\\DevelopmentFramework\\Commands\\$className")) {
            $fullClassName = "AVB\\DevelopmentFramework\\Commands\\$className";
            $commandInstance = new $fullClassName();
            // Add command to the list of available commands
            $this->commands[$commandInstance->getName()] = [
                'Command' => $commandInstance->getName(),
                'Description' => $commandInstance->getDescription(),
            ];
        } else {
            $this->logger->log("Class $className not found in file $file", 'error');
        }
    }
}

}