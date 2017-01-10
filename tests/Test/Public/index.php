<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/18 0018
 * Time: 17:24
 */


ini_set('display_errors', 'On');
error_reporting(E_ALL);




require __DIR__ . '/../../../vendor/autoload.php';




Core_Lib_App::createApp(new Test_Config_Dev())->run();
