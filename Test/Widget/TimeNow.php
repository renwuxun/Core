<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 10:04
 */

namespace Test\Widget;


use Core\Lib\Widget;

class TimeNow extends Widget{

    public function indexAction() {
        $this->assign('now', time());
        return $this->render('Widget/TimeNow');
    }

}