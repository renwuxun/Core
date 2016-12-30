<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/9/5 0005
 * Time: 15:19
 */
class Core_Helper_Logger_Udp extends Core_Lib_Logger {

    /**
     * @var Core_Helper_Net_Udp
     */
    protected $udpHandle;

    protected $facility = 1;
    protected $localhostName;

    public function __construct() {
        $this->localhostName = Core_Lib_App::App()->getConfig()->get('appId');
        $this->udpHandle = Core_Helper_Net_Udp::getInstance('localhost', 514, 2);
    }

    public function __destruct() {
        $this->udpHandle->close();
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array()) {
        if (!isset(static::$aLevels[$level])) {
            return;
        }
        if (!$this->udpHandle->isConnected()) {
            $this->udpHandle->connect();
        }
        $now = time();
        $tag = 'tag';
        if (isset($context['tag'])) {
            $tag = $context['tag'];
        }
        $message = sprintf(
            '<%d>%s % 2d %s %s %s:%s',
            $this->facility * 8 + $level,
            date('M', $now),
            date('j', $now),
            date('H:i:s', $now),
            $this->localhostName,
            $tag,
            Core_Lib_Logger::replaceContext((string)$message, $context)
        );

        $this->udpHandle->send($message);
    }


}