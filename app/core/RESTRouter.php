<?php

class RESTRouter {

    private $routes;
    private $use_compression;
    public $method;
    public $request;
    public $input;
    public $args;
    public $endpoint;
    public $verb;
    public $basePath;

    public function __construct() {
        $request = $_REQUEST;
        $this->routes = array();
        $this->basePath = null;
    }

    /* ------------------------------------------------- */

    private function headerJSON($options = '*') {
        header("Access-Control-Allow-Origin: *", true);
        header("Access-Control-Allow-Methods: $options", true);
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Content-Type: application/json");
    }

    private function headerGZip() {
        ob_start("ob_gzhandler");
    }

    private function _cleanInputs($data) {
        return $data;
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    /* ------------------------------------------------- */

    public function map($method, $route, $fn) {
        $key = str_replace(':*', '.*', $route);
        $key = str_replace(':alpha', '\w+', $key);
        $key = str_replace(':num', '[0-9]+', $key);
        $key = '#^' . $key . '$#';
        $this->routes[] = (object) array(
                    "method" => $method,
                    "route" => $route,
                    "route_regexp" => $key,
                    "fn" => $fn
        );
        if (!isset($this->route_methods[$route])) {
            $this->route_methods[$route] = array($method);
        } else {
            $this->route_methods[$route][] = $method;
        }
    }

    public function run($options = null) {


        $this->args = explode('/', rtrim($this->request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }
        $url = $_SERVER['REQUEST_URI'];
        if ($this->basePath) {
            $url = str_replace($this->basePath, "", $url);
        }
        $this->uri_path = strpos($url, '?') !== false ? substr($url, 0, strpos($url, '?')) : $url;
        $this->uri_path = rtrim($this->uri_path, "/");
        $this->method = $_SERVER['REQUEST_METHOD'];

        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            }
        }



        switch ($this->method) {
            case 'DELETE':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'POST':
                $this->request = $this->_cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'OPTIONS':
                $this->request = array();
                break;
            case 'PATCH':
            case 'PUT':
                $this->input = file_get_contents("php://input");
                
                $this->request = json_decode($this->input);
                break;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
        

        foreach ($this->routes as $route) {
            if (($route->method == $this->method || $this->method == "OPTIONS") && preg_match($route->route_regexp, $this->uri_path)) {
                
                if ($this->method == "OPTIONS") {
                    
                    $this->returnOptions($this->route_methods[$route->route]);
                    return;
                }
                $u_parts = explode('/', substr($this->uri_path, 1));
                call_user_func($route->fn, $u_parts, $this->request, $this);
                return;
            }
        }
        
        $this->returnError("Unknown method");
    }

    /* ------------------------------------------------- */

    public function useCompression($flag) {
        $this->use_compression = $flag;
    }

    public function requiredParams($params = '') {
        $list = explode(',', $params);
        foreach ($list as $prop) {
            if (!isset($this->request[$prop])) {
                return false;
            }
        }
        return true;
    }

    public function setBasePath($str = '') {
        $this->basePath = $str;
    }
    
    public function returnOptions($opts){
        $this->headerJSON(implode(',',$opts));
    }

    public function returnSuccess($data = null, $error = null) {
        if ($this->use_compression)
            $this->headerGZip();
        $this->headerJSON();
        echo json_encode(array("success" => true, "data" => $data, "error" => $error));
    }

    public function returnError($message, $data = null) {
        if ($this->use_compression)
            $this->headerGZip();
        $this->headerJSON();
        echo json_encode(array("success" => false, "data" => $data, "error" => $message));
    }

    public function returnMissingParams() {
        $this->returnError('Missing request parameters');
    }

}

?>