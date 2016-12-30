<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/18 0018
 * Time: 17:24
 */


ini_set('display_errors', 'On');

define('PROJECT_PATH', strtr(dirname(__DIR__), '\\', '/'));


$loader = require __DIR__ . '/../../../src/Core/autoload.php';

$loader->add('Test_', dirname(PROJECT_PATH));



//Core_Helper_ClassCache::run(
//    PROJECT_PATH.'/Cache/ClassesCached',
//    function(){
        Core_Lib_App::createApp(new Test_Config_Dev())->run();
//    }
//);
