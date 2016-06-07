<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 19:46
 */

namespace Core\Lib;


abstract class Interceptor {

    /**
     * @param $action string
     * @return bool
     */
    abstract public function before(&$action);

    abstract public function after();

}