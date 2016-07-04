<?php

switch ($domain) {
    case "server":
        define("DB_HOST", "localhost");
        define("DB_USERNAME", "root");
        define("DB_PASSWORD", "");
        define("DB_USE", "bassegraf");
        define("FB_APP_ID", "");
        define("FB_APP_SECRET", "");
        break;
    
    case "www.devstage.com.ar":
    case "devstage.com.ar":
        define("DB_HOST", "localhost");
        define("DB_USERNAME", "dinardip");
        define("DB_PASSWORD", "4ls1n4176o");
        define("DB_USE", "dinardip_bassegraf");
        
        define("FB_APP_ID", "");
        define("FB_APP_SECRET", "");
        break;
    
    default :
        echo "bad domain $host";
        exit(0);
}