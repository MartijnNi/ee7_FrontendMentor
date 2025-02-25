<?php

namespace AVB\DevelopmentFramework;

use AVB\DevelopmentFramework\Core\Logger;
use AVB\DevelopmentFramework\Core\CLI;

class Framework {

  protected $logger;
  protected $cli;
  protected $args = [];

  public function __construct() {
    $this->logger = new Logger();
    $this->cli = new CLI();
    $this->args = $this->cli->getArgs();
  }
  
}