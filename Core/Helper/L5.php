<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 16:31
 */


class Core_Helper_L5 {
    private $tcp;
    private static $instance;
    private function __construct() {
        $l5conf = Core_Lib_App::app()->getConfig()->get('L5Server');
        $this->tcp = new Core_Helper_Net_Tcp;
        $this->tcp->connect($l5conf['host'], $l5conf['port']);
    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param int $sid
     * @return array [ip, port]
     */
    public function route($sid) {
        $msg = Core_Helper_Net_Http::buildRequest('/get/'.$sid, '', 'GET');
        $this->tcp->send($msg);
        $retheader = Core_Helper_Net_Http::readHeader($this->tcp);
        $errno = 0;
        $errstr = '';
        $body = Core_Helper_Net_Http::readBody($this->tcp, $retheader, $errno, $errstr);
        return explode(':', trim($body));
    }

    public function __destruct() {
        $this->tcp->close();
    }
}