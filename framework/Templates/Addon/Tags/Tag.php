<?php

namespace {{namespace}}\Tags;

use {{namespace}}\Core\Controllers\TagController;
use ExpressionEngine\Service\Addon\Controllers\Tag\AbstractRoute;

class Tag extends AbstractRoute {
  public  function process() {
    $controller = new TagController();
    return $controller->create();
  }
}
