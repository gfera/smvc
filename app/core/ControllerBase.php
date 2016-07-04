<?php

class ControllerBase {

    public $view_data;
    public $user;

    /**
     *
     * @var Session
     */
    public $session;

    function __construct() {
        $this->getViewData();
        Template::$cache_dir = PATH_CACHE . "template/";
        Template::$location = PATH_VIEW;
        Template::$minify = true;
        
        $this->session = new Session();
        if ($user = $this->session->getUserCookie()) {
            $this->user = $user;
        }
    }

    private function getViewData() {
        App::loadLibrary('mobiledetect.php');
        $md = new MobileDetect();

        $this->view_data = array(
            'BASE_URL' => BASE_URL,
            'FULL_URL' => FULL_URL,
            'user' => App::getUser(),
            'meta' => array(
                "title" => "Bassegraf",
                "description" => "",
                "image" => FULL_URL . '',
                'url' => FULL_URL . implode("/",App::getURLParts()),
                "keywords" => ""
            ),
            "is_mobile" => $md->isMobile()
        );
    }

    /* --------------------------------------------------------------------- */

    public function defaultAction() {
        
    }

    /* --------------------------------------------------------------------- */

    public function loadView($view) {
        if (App::VAR_GET("debug")) {
            echo json_encode($this->view_data);
            return;
        }
        Template::loadView($view, $this->view_data);
    }

    public function loadViewTwig($view, $data) {
        App::loadLibrary('twig.php');
        $tg = new Twig();
        $tg->render($view, $data);
    }

    public static function returnJSONSuccess($data = null, $error = null) {
        echo json_encode(array("success" => true, "data" => $data, "error" => $error));
    }

    public static function returnJSONError($message, $data = null) {
        echo json_encode(array("success" => false, "data" => $data, "error" => $message));
    }

}
