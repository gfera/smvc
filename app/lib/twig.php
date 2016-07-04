<?php

class Twig {

    /** @var Twig */
    static public $inst;
    private $_parser;
    private $_twig;
    private $_template_dir;
    private $_cache_dir;
    private $_data;

    /**
     * Constructor
     *
     */
    function __construct($debug = false) {
        ini_set('include_path', PATH_LIBRARIES . 'Twig');
        require_once (string) "Autoloader.php";
        require_once (string) "FunctionCallableInterface.php";
        require_once (string) "FunctionInterface.php";
        require_once (string) "Function.php";
        require_once (string) "FilterCallableInterface.php";
        require_once (string) "FilterInterface.php";
        require_once (string) "Filter.php";

        Twig_Autoloader::register();


        $this->_template_dir = PATH_VIEW;
        $this->_cache_dir = PATH_CACHE . "/twig";

        $parser_loader = new Twig_Loader_String();
        $this->_parser = new Twig_Environment($parser_loader);

        $loader = new Twig_Loader_Filesystem($this->_template_dir);
        $this->_twig = new Twig_Environment($loader, array(
            'cache' => $this->_cache_dir,
            'debug' => true,
            'charset' => 'utf-8',
        ));
        $this->_twig->addExtension(new Twig_Extension_Debug());

        Twig::$inst = $this;
    }

    public function eraseCache() {
        echo $this->_twig->clearCacheFiles();
    }

    public function render($template, $data = array()) {
        $template = $this->_twig->loadTemplate($template);
        $this->_data = $data;
        echo $template->render($data);
    }
    
    public function getHTML($template, $data = array()) {
        $template = $this->_twig->loadTemplate($template);
        $this->_data = $data;
        return $template->render($data);
    }

    public function renderString($string, $data = null) {
        if (!$data)
            $data = $this->_data;
        return $this->_parser->loadTemplate($string)->render($data);
    }

    public function addGlobal($var_name, $data) {
        $this->_twig->addGlobal($var_name, $data);
    }

    public function addFilter($filter) {
        $this->_twig->addFilter($filter);
    }

    public function addFunction($function_name, $fn) {
        $this->_twig->addFunction(new Twig_SimpleFunction($function_name, $fn));
    }
}

?>
