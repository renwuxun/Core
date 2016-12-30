<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 18:25
 */




class Core_Helper_UUID {
    /**
     * @var Core_Helper_Net_Http
     */
    private $http;

    private static $instance;

    private $errno = 0;
    private $errstr = '';

    private $serverDown = false;

    private function __construct() {
        $conf = Core_Lib_App::app()->getConfig()->get('UUIDServer');

        $tcp = new Core_Helper_Net_Tcp;
        $tcp->setHost($conf['host'])
            ->setPort($conf['port']);
        $this->serverDown = !$tcp->connect();

        $this->http = new Core_Helper_Net_Http;
        $this->http->setTcp($tcp)->disableCookie();
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
        if ($this->serverDown) {
            list($usec, $sec) = explode(" ", microtime());
            $time_ms = $sec*1000 + $usec/1000;
            $count = rand(0, 16383);
            $gpid = 0;// php生产的uuid特有的标记
            return $time_ms<<22 | $count<<9 | $gpid;
        }
        $body = $this->http->request('/', array('User-Agent'=>'Core(php)'));
        $this->errno = $this->http->getErrno();
        $this->errstr = $this->http->getErrstr();
        return (int)trim($body);
    }

    public function __destruct() {
        $this->http->getTcp()->close();
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