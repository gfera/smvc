<?php

class FrontController extends ControllerBase {

    public function __construct() {
        parent::__construct();
    }

    public function makeModels() {
        ModelBase::generateModels();
    }

}
