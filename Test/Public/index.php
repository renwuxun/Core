<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/18 0018
 * Time: 17:24
 */


$loader = require __DIR__ . '/../../vendor/autoload.php';


define('PROJECT_PATH', strtr(dirname(__DIR__), '\\', '/'));

$loader->add('Core', dirname(PROJECT_PATH));
$loader->add('Test', dirname(PROJECT_PATH));


use Core\Lib\App;
use Test\Config\ConfigDev;


//Core\Helper\ClassCache::run(
//    PROJECT_PATH.'/Cache/ClassesCached',
//    function(){
        App::createApp(new ConfigDev())->run();
//    }
//);
