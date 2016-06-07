<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 17:54
 */




class Core_Controller_Notfound extends Core_Lib_Controller{

    public function indexAction() {

        $this->getResponse()->setStatus(404);

        return '404, Not found';
    }

}