<?php

if ($this->getAdminCookie()) {
    $this->returnJSONError("Hay una sesión de usuario activa");
    return;
}

$user = App::VAR_POST("usuario");
$pass = App::VAR_POST("clave");

$admin = Administradores::findByUserPass($user,$pass);

if ($admin) {
    App::getSession()->saveCookie("admin", Crypto::encrypt(serialize($admin)));
    $this->returnJSONSuccess();
} else {
    $this->returnJSONError("Usuario o contraseña inválidos");
}