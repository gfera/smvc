<?php

if ($this->getAdminCookie()) {
    $this->returnJSONError("Hay una sesión de usuario activa");
    return;
}
/**
 * @var Administradores
 */
$user = App::VAR_POST("usuario");

$admin = new Administradores();
$admin->usuario = ModelBase::filterString($user["usuario"]);
$admin->clave = md5($user["clave"]);
$admin->email = ModelBase::filterString($user["email"]);
$admin->nombre = ModelBase::filterString($user["nombre"]);

if(Administradores::findByUser($admin->usuario)){
    $this->returnJSONError("El usuario ya existe, elija otro nombre de usuario");
    return;
}

if(Administradores::findByEmail($admin->email)){
    $this->returnJSONError("El email ya existe, elija otro email");
    return;
}

if(Administradores::save($admin)){
    $this->returnJSONSuccess("Usuario registrado. En tu casilla de correo recibirás las instrucciones para activar tu cuenta");
} else {
    $this->returnJSONError("Hubo un error al registrar el usuario: ".ModelBase::$_db->getErrorString());
}

