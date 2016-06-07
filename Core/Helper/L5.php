<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 16:31
 */

namespace Core\Helper;


use Core\Helper\Net\Http;
use Core\Helper\Net\Tcp;
use Core\Lib\App;

class L5 {
    private $tcp;
    private static $instance;
    private function __construct() {
        $l5conf = App::app()->getConfig()->get('L5Server');
        $this->tcp = new Tcp($l5conf['host'], $l5conf['port']);
        $this->tcp->connect();
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
        $msg = Http::buildRequest('/get/'.$sid, '', 'GET');
        $this->tcp->send($msg);
        $retheader = Http::readHeader($this->tcp);
        $errno = 0;
        $errstr = '';
        $body = Http::readBody($this->tcp, $retheader, $errno, $errstr);
        return explode(':', trim($body));
    }

    public function __destruct() {
        $this->tcp->close();
    }
}