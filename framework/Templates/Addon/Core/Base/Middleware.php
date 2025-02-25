<?php
namespace {{namespace}}\Core\Base;

use {{namespace}}\Core\Http\Request;
use {{namespace}}\Core\Http\Response;
use {{namespace}}\Core\Traits\HandlesForms;

abstract class Middleware
{
    use HandlesForms;
    protected $request;
    protected $response;
    protected $addonName = '{{addonName}}';
    public function __construct() {
        // Load the language file
        ee()->lang->loadfile($this->addonName);
        ee()->load->helper('url');
        ee()->load->library('logger');
        $this->request = new Request;
        $this->response = new Response;
    }
    // Modify the request or halt the pipeline
    abstract public function handle(Request $request): Request;
}