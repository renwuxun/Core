<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/20 0020
 * Time: 16:14
 */
abstract class Core_Lib_Action {

    /**
     * @var Core_Lib_Controller
     */
    protected $controller;

    public function __construct(&$controller) {
        $this->controller = $controller;
    }

    abstract public function run();
}