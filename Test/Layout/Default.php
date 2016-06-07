<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 10:12
 */




class Test_Layout_Default extends Core_Lib_Layout{

    public function indexAction() {
        $this->assign('title', 'with layout');
        return $this->render('Layout/Default');
    }
}