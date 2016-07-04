<?php

class App {

    private static $app;
    //---------------------------------//
    private $url_parts;
    private $url;
    private $twig;
    private $view_data;
    private $php_ver;
    private $session;
    private $_db;
    private $user;

    /**
     * @var Lang 
     */
    private $lang;
    public $sitemap;

    private function __construct() {

        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('error_reporting', E_ALL ^ E_NOTICE);
        set_error_handler("App::reportError", E_ALL ^ E_NOTICE);
        set_exception_handler("App::reportError");
        register_shutdown_function("App::reportFatalError");

        $phpver = explode(".", phpversion());
        $this->php_ver = $phpver[0] * 10000 + $phpver[1] * 100 + $phpver[2];

        $this->session = new Session();
        if ($user = $this->session->getUserCookie()) {
            $this->user = $user;
        }
    }

    //**--------------------------------***/

    private function analyzeURL() {
        $url_parts = explode('?', substr($_SERVER['REQUEST_URI'], strlen(BASE_URL)));
        $this->url = $url_parts[0];
        $url_parts = explode("/", $this->url);
        $this->url_parts = $url_parts;
    }

    private function processRequest() {
        $sitemap = $this->sitemap;
        $uri = $this->url;
        $action = "defaultAction";
        $params = array();
        if (count($this->url_parts) > 0 && $this->url_parts[0] != '') {
            if (isset($sitemap[$uri])) {
                $load_controller = $sitemap[$uri];
            } else {
                foreach ($sitemap as $key => $val) {
                    $key = str_replace(':*', '.+', $key);
                    $key = str_replace(':num', '[0-9]+', $key);
                    $key = str_replace('/', "\/", $key);
                    $key = '/^' . $key . '$/';
                    if (preg_match($key, $uri)) {
                        $load_controller = $val;
                        break;
                    }
                }
            }
            if (!$load_controller)
                $load_controller = $sitemap['404'];
        } else {
            $load_controller = $sitemap['default_controller'];
        }
        if(strpos($load_controller, "/")!==false){
            $parts = explode("/", $load_controller);
            $load_controller = $parts[0];
            $action = $parts[1];
        }
        $this->loadController($load_controller, $action, $params);
    }

    public function loadController($className, $action, $params = array()) {        
        if (is_file(PATH_CONTROLLER . $className . ".php")) {
            include PATH_CONTROLLER . $className . ".php";
            $fullClassName = $className . "Controller";
            $controller = new $fullClassName;
            
            if (call_user_func_array(array($controller, $action), $params) === FALSE) {
                App::log("Controller fallo Clase:$className, metodo:$action" . FULL_URL . substr($_SERVER['REQUEST_URI'], 1));
            } else {
                
            }
        } else {
            App::log('Controller inexistente ' . $className . " | " . FULL_URL . substr($_SERVER['REQUEST_URI'], 1));
        }
    }

    private function createDBConnection() {
        $db = new MySQLDB(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_USE);
        //$db->query('SET names "utf-8"');
        $this->_db = $db;
        ModelBase::$_db = $db;
    }

    private function createLang() {
        $this->lang = new Lang();
        $this->lang->setDB($this->_db);
        //$this->lang->loadLanguage();
    }

    public function init() {
        $this->createDBConnection();
        $this->createLang();
        $this->analyzeURL();
        $this->processRequest();
    }

    //**--------------------------------***/

    public static function log($str) {
        if (!is_dir(PATH_LOG)) {
            mkdir(PATH_LOG);
            chmod(PATH_LOG, 0755);
        }
        $date = date("Y-m-d");
        $hora = date("H:i:s");
        $log_file = PATH_LOG . "log_$date.txt";

        $d = fopen($log_file, "a");
        $date = date("[Y-m-d H:i:s]");
        $err_line = "$date - $str";
        fwrite($d, "$err_line\n");
        fclose($d);
    }

    public static function reportFatalError() {
        $errors = array(E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_CORE_WARNING);
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_ERROR;
        $errline = 0;

        $error = error_get_last();
        if ($error !== NULL && array_search($error['type'], $errors) !== false) {

            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];
            App::reportError($errno, $errstr, $errfile, $errline);
            App::log('hay un 500');
            self::$app->loadController(PATH_CONTROLLER . self::$app->sitemap['500'] . '.php');
            ModelBase::$_db->close();
        }
    }

    public static function reportError($errno = null, $errstr = null, $errfile = null, $errline = null) {
        if (!is_dir(PATH_LOG)) {
            mkdir(PATH_LOG);
            chmod(PATH_LOG, 0755);
        }
        $date = date("Y-m-d");
        $hora = date("H:i:s");
        $log_file = PATH_LOG . "error_$date.txt";

        $d = fopen($log_file, "a");
        $date = date("[Y-m-d H:i:s]");
        $err_line = "$date ";
        switch ($errno) {
            case E_USER_ERROR:
                $err_line.= "My ERROR</b> [$errno] $errstr<br />\n";
                $err_line.= "  Fatal error on line $errline in file $errfile";
                $err_line.= "Aborting...<br />\n";
                break;

            case E_USER_WARNING:
                $err_line.="My WARNING</b> [$errno - $errfile:$errline]\n$errstr\n";
                break;

            case E_USER_NOTICE:
                $err_line.="My NOTICE</b> [$errno - $errfile:$errline]\n$errstr\n";
                break;
            case E_DEPRECATED:
                $err_line.="DEPRECATED</b> [$errno - $errfile:$errline]\n$errstr\n";
                break;
            case E_NOTICE:
                $err_line.="NOTICE</b> [$errno - $errfile:$errline]\n$errstr\n";
                break;
            default:
                $err_line.="Unknown error [$errno - $errfile:$errline]\n$errstr\n";
                break;
        }

        fwrite($d, "$err_line\n");
        fclose($d);
    }

    public static function loadLibrary($path, $all = false) {
        if ($all) {
            $files = glob(PATH_LIBRARIES . $path . '/*.php');
            foreach ($files as $file) {
                require_once( $file );
            }
            return;
        }
        require_once(PATH_LIBRARIES . $path);
    }

    /**
     * 
     * @param string $path
     * @return boolean
     */
    public static function loadModel($path) {
        if (file_exists(PATH_MODELS . $path . '.php')) {
            require_once(PATH_MODELS . $path . '.php');
            return true;
        }
        if (file_exists(PATH_MODELS . "base/" . $path . '.php')) {
            require_once(PATH_MODELS . "base/" . $path . '.php');
            return true;
        }
        return false;
    }

    public static function loadView($view, $direct = false) {
        if ($direct) {
            if (file_exists($view)) {
                echo file_get_contents($view);
                return;
            }
        } else if (file_exists(PATH_VIEW . $view)) {
            echo file_get_contents(PATH_VIEW . $view);
            return;
        }
        echo 'View not found';
    }

    public static function VAR_GET($var) {
        return isset($_GET[$var]) ? $_GET[$var] : null;
    }

    public static function VAR_POST($var) {
        return isset($_POST[$var]) ? $_POST[$var] : null;
    }

    public static function requestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function userLanguage() {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    /**
     * 
     * @return array
     */
    public static function getURLParts($idx=null) {
        if(is_int($idx)) return self::$app->url_parts[$idx];
        return self::$app->url_parts;
    }

    /**
     * @return App
     */
    public static function getApp() {
        if (!self::$app)
            self::$app = new App();
        return self::$app;
    }

    /**
     * 
     * @return UsuarioSite
     */
    public static function getUser() {
        return self::$app->user;
    }

    public static function getViewData() {
        return self::$app->view_data;
    }

    /**
     * 
     * @return Lang
     */
    public static function getLang() {
        return self::$app->lang;
    }

    public static function reloadUser() {
        if (self::$app->session->varGet("usuario")) {
            self::$app->user = unserialize(self::$app->session->varGet("usuario"));
            self::$app->twig->addGlobal("usuario", self::$app->user->getJSON());
        }
    }

    /**
     * @return Session
     */
    public static function getSession() {
        return self::$app->session;
    }

}

spl_autoload_register('App::loadModel');

include PATH_APP . "config/globals.php";
include PATH_APP . "config/db.php";
include PATH_APP . "config/routes.php";

include PATH_APP . "core/ControllerBase.php";
include PATH_APP . "core/ModelBase.php";
include PATH_APP . "core/RESTRouter.php";
include PATH_APP . "core/Lang.php";
include PATH_APP . "core/Template.php";

App::loadLibrary("MySQLDB.php");
App::loadLibrary("crypto.php");
App::loadLibrary("utils.php");
App::loadLibrary("session.php");
App::getApp()->sitemap = $sitemap;
App::getApp()->init();
ModelBase::$_db->close();
