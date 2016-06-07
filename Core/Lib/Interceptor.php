<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 19:46
 */



abstract class Core_Lib_Interceptor {

    /**
     * @param $action string
     * @return bool
     */
    abstract public function before(&$action);

    abstract public function after();

}