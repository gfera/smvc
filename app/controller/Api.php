<?php

class ApiController extends ControllerBase {

    public function defaultAction() {
        
        $url_parts = App::getURLParts();
        $view_data = array();
        if (count($url_parts) > 1) {
            $file_to_include = $this->checkPathType($url_parts[1], $url_parts, 1);
            if ($file_to_include) {
                include $file_to_include;
            } else {
                $this->noAction();
            }
        } else {
            $this->noAction();
        }
    }

    function noAction() {
        $this->returnJSONError("invalid action");
    }

    function api_missingParams() {
        $this->returnJSONError("invalid parameters");
    }

    /**
     * 
     * @return UsuarioAdmin
     */
    function requireLogin() {
        if (!App::getUser()) {
            returnJSONError("Uknown user");
            return false;
        }
        return true;
    }

    function checkPathType($path, $url_parts, $part_index) {
        $try_folder = PATH_CONTROLLER . 'api/' . $path;
        $try_file = PATH_CONTROLLER . 'api/' . $path . '.php';

        if (is_file($try_file))
            return $try_file;

        $part_index++;
        if (is_dir($try_folder) && count($url_parts) > $part_index) {
            return $this->checkPathType($path . '/' . $url_parts[$part_index], $url_parts, $part_index);
        }
        return null;
    }

}
