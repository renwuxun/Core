<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/26 0026
 * Time: 18:25
 */

namespace Core\Helper;


use Core\Helper\Net\Http;
use Core\Helper\Net\Tcp;
use Core\Lib\App;

class UUID {
    /**
     * @var Tcp
     */
    private $tcp;

    private static $instance;

    private function __construct() {
        $l5conf = App::app()->getConfig()->get('UUIDServer');
        $this->tcp = new Tcp($l5conf['host'], $l5conf['port']);
        $this->tcp->connect();
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
        $msg = Http::buildRequest('/', '', 'GET');
        $this->tcp->send($msg);
        $body = $this->tcp->fgets(512);
        return (int)trim($body);
    }

    public function __destruct() {
        $this->tcp->close();
    }
}