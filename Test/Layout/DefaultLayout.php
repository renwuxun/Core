<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 10:12
 */

namespace Test\Layout;


use Core\Lib\Layout;

class DefaultLayout extends Layout{

    public function indexAction() {
        $this->assign('title', 'with layout');
        return $this->render('Layout/DefaultLayout');
    }
}