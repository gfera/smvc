<?php

class Template {

    public static $location;
    public static $cache_dir;
    public static $minify;
    public static $debug;

    static private function loadCacheFile($_____View, $_____Params) {
        include $_____View;
    }

    static private function processFile($code, &$files) {
        $match = array();
        $ret = $code;
        preg_match_all("<\?php[ ]*inject\([\"']+([ a-z0-9A-Z.\/]*)[\"']+\)[; ]*\?>", $code, $match);
        foreach ($match[0] as $key => $value) {
            $ret = str_replace("<$value>", self::loadViewHTML(self::processFile($match[1][$key], $files)), $ret);
            $files[] = $match[1][$key];
        }
        if (self::$minify) {
            $ret = preg_replace(array('/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'), array(' ', ''), $ret);
        }
        return $ret;
    }

    public static function loadViewHTML($view) {
        return file_get_contents(self::$location . $view);
    }

    public static function loadView($view, $params) {
        $rebuild = false;
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir);
        }
        if ($ot = filemtime(self::$location . $view)) {
            $cache_file = self::$cache_dir . preg_replace("/[.-\/]*/i", "", $view);
            $cache_file_check = $cache_file."_";
            $files = explode(",",file_get_contents($cache_file_check));
            $files[]=$view;
            $ct = (int) filemtime($cache_file);
            foreach($files as $f){
                if(filemtime(self::$location . $f) > $ct){
                    $rebuild = true;
                }
            }
            
            if ($rebuild || self::$debug) {
                $file = '<?php; foreach ($_____Params as $key => $value) { ${$key} = $value; };?>';
                $tmp = file_get_contents(self::$location . $view);
                $files = array();
                $tmp = self::processFile($tmp, $files);
                $file.=$tmp;
                file_put_contents($cache_file_check, implode(",", $files));
                file_put_contents($cache_file, $file);
            }
            self::loadCacheFile($cache_file, $params);
        } else {
            echo "La vista no existe!";
        }
    }

}
