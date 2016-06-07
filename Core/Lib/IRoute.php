<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 19:18
 */



interface Core_Lib_IRoute {
    /**
     * @param $path string
     */
    public function init($path);

    /**
     * @return string
     */
    public function getControllerName();

    /**
     * @return string
     */
    public function getActionName();

    /**
     * @return array
     */
    public function getArgs();
}