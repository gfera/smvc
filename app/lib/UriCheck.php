<?php

class UriCheck {

    static private $_forbidden_words = array(
        "404",
        "500",
        "api",
        "teaser",
        "earlybird",
        "terms-and-privacy",
        "edit-profile",
        "dashboard",
        "user",
        "account",
        "contact",
        "search",
        "logout",
        "comingsoon"
    );
    static private $_disallowed_chars = "/[^a-z0-9\-\.]*/i";

    public static function isValid($uri) {
        $ret = preg_match(self::$_disallowed_chars, $uri);
        return $ret == 1 && strlen($uri) > 0;
    }

    public static function toUri($uri) {
        $ret = array();
        $_uri = strtolower(Utils::transliterateString($uri));
        $_uri = str_replace(self::$_disallowed_chars, '', $_uri);
        return str_replace(' ', '.', $_uri);
    }

}
