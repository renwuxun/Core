<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 18:25
 */




class Core_Helper_UUID {
    /**
     * @var Core_Helper_Net_Tcp
     */
    private $tcp;

    private static $instance;

    private $errno = 0;
    private $errstr = '';

    private function __construct() {
        $l5conf = Core_Lib_App::app()->getConfig()->get('UUIDServer');
        $this->tcp = new Core_Helper_Net_Tcp;
        $this->tcp->connect($l5conf['host'], $l5conf['port']);
    }

    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return int
     */
    public function get() {
        $msg = Core_Helper_Net_Http::buildRequest('/', '', 'GET');
        $this->tcp->send($msg);
        $header = Core_Helper_Net_Http::readHeader($this->tcp);
        $body = Core_Helper_Net_Http::readBody($this->tcp, $header, $this->errno, $this->errstr);
        return (int)trim($body);
    }

    public function __destruct() {
        $this->tcp->close();
    }

    /**
     * @return int
     */
    public function getErrno() {
        return $this->errno;
    }

    /**
     * @return string
     */
    public function getErrstr() {
        return $this->errstr;
    }
}