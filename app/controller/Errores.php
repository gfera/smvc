<?php

class ErroresController extends ControllerBase {

    public function e404() {
        ob_clean();
        header("HTTP/1.1 404 Not Found", true, 404);
        $this->view_data["error"]="Página no encontrada.";
        $this->loadView("error.php");
    }
    public function e500() {
        ob_clean();
        header("HTTP/1.1 500 Internal Server Error", true, 500);
        $this->view_data["error"]="Error interno de servidor.";
        $this->loadView("error.php");
    }

}
