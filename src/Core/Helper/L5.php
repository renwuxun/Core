<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 16:31
 */


class Core_Helper_L5 {

    /**
     * @var Core_Helper_Net_Http
     */
    private $http;
    private static $instance;

    private function __construct() {
        $l5conf = Core_Lib_App::app()->getConfig()->get('L5Server');

        $tcp = new Core_Helper_Net_Tcp;
        $tcp->setHost($l5conf['host'])
            ->setPort($l5conf['port']);

        $this->http = new Core_Helper_Net_Http;
        $this->http->setTcp($tcp)->disableCookie();
    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param int $sid
     * @return array(ip, port)
     */
    public function route($sid) {
        $body = $this->http->request(
            '/get/'.$sid,
            array(
                'User-Agent'=>'Core(php)'
            )
        );
        if ($this->http->getStatusCode() == 200) {
            return explode(':', trim($body));
        }
        return array();
    }

    public function __destruct() {
        $this->http->getTcp()->close();
    }
}