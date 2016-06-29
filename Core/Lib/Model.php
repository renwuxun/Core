<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:49
 */



abstract class Core_Lib_Model extends Core_Lib_DataObject {

    public function __set($name, $value) {
        $m = 'set'.ucfirst($name);
        if (method_exists($this, $m)) {
            $this->$m($value);
        } else {
            throw new Exception('property '.get_class().'::'.$name.' access deny');
        }
    }

    public function __get($name) {
        $m = 'get'.ucfirst($name);
        if (method_exists($this, $m)) {
            $this->$m();
        } else {
            throw new Exception('property '.get_class().'::'.$name.' access deny');
        }
    }
}