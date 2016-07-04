<?php

$nl = new Newsletter();
$nl->email = Newsletter::filterString(App::VAR_POST("email"));
$nl->fecha = Newsletter::now_timestamp();
$nl->activo = 1;
$nl->ip = $_SERVER["REMOTE_ADDR"];

if(Newsletter::findBy("email = '$nl->email'")){
    self::returnJSONError("El Email ya existe en nuestra base de datos.");
    return;
}

if(Newsletter::save($nl)){
    self::returnJSONSuccess("E-mail guardado. Pronto recibirás nuestras novedades!");
} else {
    self::returnJSONError("No se pudo guardar el email, intentá mas tarde.");
}