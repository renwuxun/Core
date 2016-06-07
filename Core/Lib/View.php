<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 22:10
 */

namespace Core\Lib;


class View {

    private $__ = array();
    private $__basepath = '';

    public function init($basepath = '') {
        if ($basepath != '') {
            $this->__basepath = $basepath;
        } else {
            $this->__basepath = PROJECT_PATH . '/View';
        }
    }

    public function assign($key, $value) {
        $this->__[$key] = $value;
    }

    public function render($__sView) {
        ob_start();
        extract($this->__, EXTR_PREFIX_SAME, '__');
        require($this->__basepath . '/' . $__sView . '.php');
        $buf = ob_get_contents();
        ob_end_clean();
        return $buf;
    }

    /**
     * @param Widget $widget
     * @return string
     */
    protected function widget($widget) {
        return $widget->run('index');
    }

    public function __destruct() {
        $this->__ = null;
        $this->__basepath = null;
    }
}