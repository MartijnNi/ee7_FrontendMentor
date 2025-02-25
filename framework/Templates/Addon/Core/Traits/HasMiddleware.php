<?php
namespace {{namespace}}\Core\Traits;

use {{namespace}}\Core\Http\Request;

trait HasMiddleware
{
    protected $request;

    public function __construct() {
        // Call the parent constructor if it exists
        $parent_class = get_parent_class($this);
        if ($parent_class && method_exists($parent_class, '__construct')) {
            parent::__construct();
        }
        $this->middleware();
    }

    protected function middleware()
    {
        if (isset($this->middleware) && !empty($this->middleware)) {
            $this->request = new Request();
            foreach ($this->middleware as $middleware) {
                $middlewareInstance = new $middleware();
                $this->request = $middlewareInstance->handle($this->request);
            }
        }
    }
}