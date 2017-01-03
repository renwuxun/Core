<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 16:54
 */




class Test_Config_Dev extends Core_Lib_Config {
    public static function config() {
        return array(
            'logger'=>'Test_Lib_FileLogger'
        );
    }
}