<?php

$protocol = isset($_SERVER["HTTPS"]) ? "https" : "http";
$domain = $host = $_SERVER["HTTP_HOST"];
$port = "";
if (strpos($host, ":") !== false) {
    $domain = explode(":", $host);
    $port = ":".$domain[1];
    $domain = $domain[0];
    
}


$curr_path = dirname(__FILE__) . '/';

define('PATH_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('PATH_CONFIG', PATH_APP . 'config/');
define('PATH_CONTROLLER', PATH_APP . 'controller/');
define('PATH_MODELS', PATH_APP . 'model/');
define('PATH_VIEW', PATH_APP . 'view/');
define('PATH_CACHE', PATH_APP . 'cache/');
define('PATH_LOG', PATH_APP . 'log/');
define('PATH_LIBRARIES', PATH_APP . 'lib/');
define("BASE_URL", str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define("FULL_URL", "$protocol://$domain$port" . BASE_URL);


define("PATH_UPLOADS", PATH_DOCUMENT);

define("GOOGLE_API_KEY", "");
define("GOOGLE_API_KEY_WEB", "");

define("GOOGLE_API_OAUTH_ID", "");
define("GOOGLE_API_OAUTH_SECRET", "");
define("GOOGLE_API_OAUTH_CALLBACK", FULL_URL . 'api/oauth/google/callback');


define("FACEBOOK_APP_ID", "");
define("FACEBOOK_APP_SECRET", "");
define("FACEBOOK_CALLBACK", FULL_URL . 'api/oauth/facebook/callback');

define("LINKEDIN_CLIENT_ID", "");
define("LINKEDIN_CLIENT_SECRET", "");
define("LINKEDIN_REDIRECT", FULL_URL . 'api/oauth/linkedin/callback');


switch ($domain) {
   
    case "www.basegraf.com":
        define('SITE_PRODUCTION', true);
        break;
    default:
        define('SITE_PRODUCTION', false);
}