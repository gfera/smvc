<?php

/*spl_autoload_register(
    function ($className) {
      $classPath = explode('\\', $className);
      if ($classPath[0] != 'Happyr') {
        return;
      }
      $classPath = array_slice($classPath, 2);

      $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
      if (file_exists($filePath)) {
        require_once($filePath);
      }
    }
);*/

App::loadLibrary('linkedin/Client.php');
App::loadLibrary('linkedin/Exception.php');
