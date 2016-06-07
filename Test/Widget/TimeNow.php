<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 10:04
 */




class Test_Widget_TimeNow extends Core_Lib_Widget{

    public function indexAction() {
        $this->assign('now', time());
        return $this->render('Widget/TimeNow');
    }

}