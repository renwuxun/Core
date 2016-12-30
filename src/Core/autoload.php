<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/6/6 0006
 * Time: 17:49
 */


$loader = require __DIR__ . '/../../vendor/autoload.php';

$loader->add('Core_', dirname(__DIR__));

return $loader;