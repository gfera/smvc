<?php

if($this->getAdminCookie()){
    App::getSession()->saveCookie("admin",null);
    $this->returnJSONSuccess();
} else {
    $this->returnJSONError("No hay una sesión de usuario activa");
}