<?php

namespace AppBundle\Factory;

/**
 * Created by PhpStorm.
 * User: kusiu
 * Date: 10/11/2015
 * Time: 20:18
 */
class ShipFactory
{

    public static function create($ship = null) {
        if(!is_null($ship)) {
            try {
                $name = "AppBundle\\Entity\\$ship";
                return new $name;
            } catch(Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

}