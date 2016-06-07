<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 17:54
 */

namespace Core\Controller;


use Core\Lib\Controller;

class Notfound extends Controller{

    public function indexAction() {

        $this->getResponse()->setStatus(404);

        return '404, Not found';
    }

}