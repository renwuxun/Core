<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/9/5 0005
 * Time: 14:12
 */
abstract class Core_Lib_Logger {

    const LV_EMERGENCY = 0; // 系统不可用
    const LV_ALERT     = 1; // **必须**立刻采取行动，在整个网站都垮掉了、数据库不可用了或者其他的情况下，**应该**发送一条警报短信把你叫醒。
    const LV_CRITICAL  = 2; // 紧急情况，程序组件不可用或者出现非预期的异常。
    const LV_ERROR     = 3; // 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
    const LV_WARNING   = 4; // 出现错误性的一场，使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
    const LV_NOTICE    = 5; // 一般性重要的事件。
    const LV_INFO      = 6; // 重要事件，如：用户登录和SQL记录。
    const LV_DEBUG     = 7; // debug

    public static $aLevels = array(
        self::LV_EMERGENCY => 'emergency',
        self::LV_ALERT     => 'alert',
        self::LV_CRITICAL  => 'critical',
        self::LV_ERROR     => 'error',
        self::LV_WARNING   => 'warning',
        self::LV_NOTICE    => 'notice',
        self::LV_INFO      => 'info',
        self::LV_DEBUG     => 'debug',
    );

    /**
     * 任意等级的日志记录
     *
     * @param int $level
     * @param string $message
     * @param array $context
     */
    abstract public function log($level, $message, array $context = array());

    /**
     * 系统不可用
     *
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array()) {
        $this->log(self::LV_EMERGENCY, $message, $context);
    }

    /**
     * **必须**立刻采取行动
     *
     * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下，**应该**发送一条警报短信把你叫醒。
     *
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array()) {
        $this->log(self::LV_ALERT, $message, $context);
    }

    /**
     * 紧急情况
     *
     * 例如：程序组件不可用或者出现非预期的异常。
     *
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array()) {
        $this->log(self::LV_CRITICAL, $message, $context);
    }

    /**
     * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
     *
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array()) {
        $this->log(self::LV_ERROR, $message, $context);
    }

    /**
     * 出现非错误性的异常。
     *
     * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
     *
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array()) {
        $this->log(self::LV_WARNING, $message, $context);
    }

    /**
     * 一般性重要的事件。
     *
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array()) {
        $this->log(self::LV_NOTICE, $message, $context);
    }

    /**
     * 重要事件
     *
     * 例如：用户登录和SQL记录。
     *
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array()) {
        $this->log(self::LV_INFO, $message, $context);
    }

    /**
     * debug 详情
     *
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array()) {
        $this->log(self::LV_DEBUG, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    public static function replaceContext($message, array $context = array()) {
        $_context = array();
        foreach ($context as $k => $v) {
            $_context['{'.$k.'}'] = $v;
        }
        unset($context);
        return strtr($message, $_context);
    }
}