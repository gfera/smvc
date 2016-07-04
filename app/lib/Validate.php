<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Validate
 *
 * @author german
 */
class Validate {
    
    public static function email($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
}
