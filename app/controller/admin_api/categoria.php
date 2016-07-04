<?php

$rest = new RESTRouter();
$rest->basePath = "/admin/api/categoria";

/*
  Create -> Post
  Read   -> Get
  Update -> Put
  Delete -> Delete
 */

$rest->map("GET", "/list", function(Array $parts, Array $req, RESTRouter $r) {
    $r->returnSuccess(Categorias::getList());
});

$rest->map("GET", "/:num", function(Array $parts, Array $req, RESTRouter $r) {
    $r->returnSuccess(Categorias::findById($parts[0]));
});

$rest->map("GET", "/:num/productos", function(Array $parts, Array $req, RESTRouter $r) {
    $r->returnSuccess(Productos::findByCategory($parts[0]));
});

$rest->map("POST", "/", function(Array $parts, Array $req, RESTRouter $r) {
    if (!AdminController::api_requiere_login()) {
        return;
    }
    $c = new Categorias($req);
    Categorias::filterForDB($c);
    $r->returnSuccess($c);
});

$rest->map("PUT", "/:num", function(Array $parts, Array $req, RESTRouter $r) {
    if (!AdminController::api_requiere_login()) {
        return;
    }

    $c = Categorias::findById($parts[0]);
    if (!$c) {
        $r->returnError("Categoría desconocida");
    } else {
        Categorias::fetch($c, $req);
        $c->id = $parts[0];
        if (Categorias::save($c)) {
            $r->returnSuccess($c);
        } else {
            $r->returnError(Categorias::$_db->getErrorString());
        }
    }
});

$rest->map("DELETE", "/:num", function(Array $parts, Array $req, RESTRouter $r) {
    if (!AdminController::api_requiere_login()) {
        return;
    }
    if (Categorias::deleteById($parts[0])) {
        $r->returnSuccess();
    } else {
        $r->returnError(Categorias::$_db->getErrorString());
    }
});

$rest->run();
