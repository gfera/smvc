<?php

class Session {

    private $session_name = 'mss';
    private $cookie_name = 'msc';
    private $meta = '_ff';
    private $started;
    private $user;

    public function __construct() {
        session_name($this->session_name);
        //session_set_cookie_params(60 * 60 * 24 * 180); //Que la cookie dure 30 días

        if (ini_get('session.auto_start')) {
            $this->started = true;
            $this->start();
        } else {
            $this->start();
        }
    }

    /* ------------------------------------------------ */

    private function init() {
        $_SESSION[$this->meta] = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'name' => session_name(),
            'created' => $_SERVER['REQUEST_TIME'],
            'activity' => $_SERVER['REQUEST_TIME'],
        );
    }

    private function get($name, $default = NULL) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    /* ------------------------------------------------ */

    public function close() {
        @session_write_close();
    }

    public function start() {
        $this->started || session_start();
        (isset($_SESSION[$this->meta]) || $this->init()) || $_SESSION[$this->meta]['activity'] = $_SERVER['REQUEST_TIME'];
        $this->started = true;
    }

    public function destroy() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            $duration = -60 * 60 * 24 * 7; // una semana para atrás
            setcookie($this->cookie_hame, '', time() - $duration, "/", $params["domain"], false, false);
        }
        session_destroy();
    }

    /* -------------------------------------------------- */

    public function varExist($offset) {
        $this->started || $this->start();
        return isset($_SESSION[$offset]);
    }

    public function varGet($offset) {
        $this->started || $this->start();
        return $this->get($offset);
    }

    public function varSet($offset, $value) {
        $this->started || $this->start();
        $_SESSION[$offset] = $value;
    }

    public function varUnset($offset) {
        unset($_SESSION[$offset]);
    }

    /* ------------------------------------------------ */

    public function saveCookie($name, $value) {
        if (ini_get("session.use_cookies")) {
            $duration = 60 * 60 * 24 * 180; // medio año
            $params = session_get_cookie_params();
            setcookie($name, $value, time() + $duration, "/", $params["domain"], false, false);
        }
    }

    public function getCookie($name) {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
    }

    /* ------------------------------------------------ */

    public function updateUserCookie() {
        $this->saveCookie($this->cookie_name, $this->getCookie($this->cookie_name));
    }

    public function getUserCookie() {
        $data = $this->getCookie($this->cookie_name);
        if($data) $this->user = unserialize(Crypto::decrypt($data));
        return $this->user;
    }

    public function setUserCookie($user) {
        if ($user) {
            $data = Crypto::encrypt(serialize($user));
            $this->saveCookie($this->cookie_name, $data);
        } else {
            $this->saveCookie($this->cookie_name, '');
        }
    }

}

?>