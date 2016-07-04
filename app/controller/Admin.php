<?php

class AdminController extends ControllerBase {

    function api() {
        $url_parts = App::getURLParts();
        $view_data = array();
        
        if (count($url_parts) > 2) {
            $file_to_include = $this->api_checkPathType($url_parts[2], $url_parts, 2);
            if ($file_to_include) {
                include $file_to_include;
            } else {
                $this->api_noAction();
            }
        } else {
            $this->api_noAction();
        }
    }

    function api_noAction() {
        $this->returnJSONError("invalid action");
    }

    function api_missingParams() {
        $this->returnJSONError("invalid parameters");
    }
    
    
    function api_checkPathType($path, $url_parts, $part_index) {
        $try_folder = PATH_CONTROLLER . 'admin_api/' . $path;
        $try_file = PATH_CONTROLLER . 'admin_api/' . $path . '.php';

        if (is_file($try_file))
            return $try_file;

        $part_index++;
        if (is_dir($try_folder) && count($url_parts) > $part_index) {
            return $this->api_checkPathType($path . '/' . $url_parts[$part_index], $url_parts, $part_index);
        }
        return null;
    }

    /**
     * 
     * @return Administradores
     */
    public static function getAdminCookie() {
        $encrypted_cookie = App::getSession()->getCookie("admin");
        if (!$encrypted_cookie)
            return null;
        return unserialize(Crypto::decrypt($encrypted_cookie));
    }

    /**
     * 
     * @return boolean
     */
    public static function api_requiere_login() {
        if (!$this->getAdminCookie()) {
            returnJSONError("Uknown user");
            return false;
        }
        return true;
    }


}
