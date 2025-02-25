<?php
namespace {{namespace}}\Core\Http;

class Request
{
    private $method;
    private $headers;
    private $data;
    private $uri;
    private $files;

    public function __construct()
    {
        // Check for method spoofing
        if (!empty($_POST['_method'])) {
            $this->method = strtoupper($_POST['_method']);
        } else {
            $this->method = $_SERVER['REQUEST_METHOD'];
        }

        $this->headers = json_decode(json_encode(getallheaders()), false);
        $this->data = $this->capture();
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->files = $_FILES;
    }

    private function capture()
    {
        switch ($this->method) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                if ($this->isAjax() || $this->isJson()) {
                    return json_decode(json_encode(json_decode(file_get_contents('php://input'))), false);
                }
                return json_decode(json_encode($_POST), false);
            case 'GET':
                return json_decode(json_encode($_GET), false);
            default:
                return new \stdClass();
        }
    }

    public function method()
    {
        return $this->method;
    }

    public function header($key)
    {
        return $this->headers->$key ?? null;
    }

    public function headers()
    {
        return $this->headers;
    }

    public function bearer()
    {
        $authHeader = $this->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function input($key, $default = null)
    {
        return $this->data->$key ?? $default;
    }

    public function query($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function ip()
    {
        // Advanced IP detection to handle proxy and load balancers
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function referer()
    {
        return $this->header('Referer');
    }

    public function isJson()
    {
        $contentType = $this->header('Content-Type');
        return $contentType && (strpos($contentType, 'application/json') !== false);
    }

    public function has($key)
    {
        return isset($this->data->$key) || isset($_GET[$key]);
    }

    public function all()
    {
        return array_merge((array) $this->data, $_GET);
    }

    public function isSecure()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function isAjax()
    {
        return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }

    public function isMethod($method)
    {
        return strtolower($this->method) === strtolower($method);
    }

    public function isPost()
    {
        return $this->isMethod('POST');
    }

    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    public function isGet()
    {
        return $this->isMethod('GET');
    }

    // Handle CORS origin check
    public function origin()
    {
        return $this->header('Origin');
    }

    // Handle CORS preflight (OPTIONS request)
    public function isPreflight()
    {
        return $this->method() === 'OPTIONS';
    }

    // Check if request includes a CSRF token
    public function csrf()
    {
        return $this->header('X-Csrf-Token') ?? $this->input('csrf_token');
    }

    // Optional debugging method
    public function dump()
    {
        return [
            'method' => $this->method,
            'headers' => $this->headers,
            'data' => $this->data,
            'uri' => $this->uri,
            'files' => $this->files,
            'ip' => $this->ip(),
            'isSecure' => $this->isSecure(),
            'isAjax' => $this->isAjax(),
            'origin' => $this->origin(),
            'csrfToken' => $this->csrf(),
        ];
    }
}
