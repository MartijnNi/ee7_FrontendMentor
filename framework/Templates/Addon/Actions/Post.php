<?php

namespace {{namespace}}\Actions;

use {{namespace}}\Core\Controllers\TagController;
use {{namespace}}\Core\Middleware\FormValidationMiddleware;
use {{namespace}}\Core\Traits\HasMiddleware;
use ExpressionEngine\Service\Addon\Controllers\Action\AbstractRoute;

class Post extends AbstractRoute
{
  use HasMiddleware;  
  protected $middleware = [
      FormValidationMiddleware::class,
  ];
  
  public function process() {
   
    $controller = new TagController();
    $controller->update();
  } 
}
