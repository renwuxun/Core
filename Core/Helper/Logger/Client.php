<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/9/5 0005
 * Time: 15:11
 */
class Core_Helper_Logger_Client extends Core_Lib_Logger {

    public static $aLevels = array(
        self::LV_EMERGENCY => 'error',
        self::LV_ALERT     => 'error',
        self::LV_CRITICAL  => 'error',
        self::LV_ERROR     => 'error',
        self::LV_WARNING   => 'warn',
        self::LV_NOTICE    => 'info',
        self::LV_INFO      => 'info',
        self::LV_DEBUG     => 'log',
    );

    public function __construct() {
    }

    /**
     * @var array
     */
    protected $logs = [];

    public function log($level, $message, array $context = array()) {
        $this->logs[] = [date('Y-m-d H:i:s'), self::$aLevels[$level], $message];
    }

    public function __destruct() {
        if(!empty($this->logs)) {
            echo '<script type="text/javascript">';
            echo 'try{';
            foreach($this->logs as $log) {
                echo 'console.'.$log[1].'("'.$log[0].' '.addslashes((string)$log['2']).'");';
            }
            echo '}catch(e){}';
            echo '</script>';
        }
    }
}