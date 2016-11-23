<?php
/**
 * Created by PhpStorm.
 * User: zhangleo
 * Date: 16/11/23
 * Time: 上午11:27
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Core_Lib_Logger
{
    private $logger;
    private $dir;
    function __construct($name, $dir)
    {
        $this->logger = new Logger($name);
        $this->dir = $dir;
    }

    public function addError($fileName, $content)
    {
        $this->addPush($fileName, Logger::ERROR);
        $this->logger->addError($this->getContent($content));
    }

    public function addInfo($fileName, $content)
    {
        $this->addPush($fileName, Logger::INFO);
        $this->logger->addInfo($this->getContent($content));
    }

    private function addPush($fileName, $level)
    {
        $this->logger->pushHandler(new StreamHandler($this->dir.'/'.$fileName.'.log', $level));
        $this->logger->pushHandler(new FirePHPHandler());
    }

    private function getContent($content)
    {
        if(is_array($content)) {
            $content = json_encode($content);
        }
        return $content;
    }
}