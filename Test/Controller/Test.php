<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 5:27
 */

namespace Test\Controller;


use Core\Lib\App;
use Core\Lib\Controller;
use Test\Layout\DefaultLayout;

class Test extends Controller {

    protected static function selfInterceptors() {
    }

    public function indexAction() {
        $this->setLayout(new DefaultLayout());
        return $this->render('Test/Index');
    }

    public function jsonAction() {

        return $this->renderJsonCb(['ab'=>'sfds'],200);
    }

    public function hbAction() {
        App::app()->getResponse()->setHeader('Content-Type', 'text/plain;charset=utf-8');
        return 'test/hbdf';
    }
}