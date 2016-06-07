<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 22:16
 */

namespace Core\Lib;


abstract class Layout extends Widget {

    protected static function viewPath() {
        return PROJECT_PATH . 'View/Layout/';
    }

}