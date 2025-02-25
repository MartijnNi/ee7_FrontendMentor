<?php
namespace {{namespace}}\Core\Base;

use {{namespace}}\Service\Helpers;
use {{namespace}}\Core\Http\Request;
use {{namespace}}\Core\Http\Response;
use {{namespace}}\Core\Traits\HasMiddleware;

abstract class Controller
{
    protected $request;
    protected $response;
    protected $middleware = [];
    protected $addonName = '{{addonName}}';
    protected $helpers;

    use HasMiddleware;

    public function __construct()
    {
        ee()->load->helper('url');
        $this->request = new Request();
        $this->response = new Response();
        $this->helpers = new Helpers(); 
    }
}
