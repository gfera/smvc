<?php

$rest = new RESTRouter();
$rest->basePath = "/admin/api/producto";

$rest->map("GET", "/list", function(Array $parts, Array $req, RESTRouter $router) {

    $router->returnSuccess(Productos::getList());
});

$rest->map("GET", "/:num", function(Array $parts, Array $req, RESTRouter $router) {
    $router->returnSuccess(Productos::findById($parts[0]));
});

$rest->map("POST", "/", function(Array $parts, Array $req, RESTRouter $router) {
    if (!AdminController::api_requiere_login()) {
        return;
    }
});

$rest->map("PUT", "/:num", function(Array $parts, Array $req, RESTRouter $r) {
    if (!AdminController::api_requiere_login()) {
        return;
    }
    $c = Productos::findById($parts[0]);
    if (!$c) {
        $r->returnError("Categoría desconocida");
    } else {
        Productos::fetch($c, $req);
        $c->productoID = $parts[0];
        if (Productos::save($c)) {
            $r->returnSuccess($c);
        } else {
            $r->returnError(Productos::$_db->getErrorString());
        }
        $r->returnSuccess($c);
    }
});

$rest->map("DELETE", "/:num", function(Array $parts, Array $req, RESTRouter $r) {
    if (!AdminController::api_requiere_login()) {
        return;
    }
    if (Productos::deleteById($parts[0])) {
        $r->returnSuccess();
    } else {
        $r->returnError(Productos::$_db->getErrorString());
    }
});

$rest->run();
