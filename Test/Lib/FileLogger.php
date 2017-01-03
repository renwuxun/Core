<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/11/23 0023
 * Time: 15:16
 */
class Test_Lib_FileLogger extends Core_Helper_Logger_File {
    /**
     * @param int $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array()) {
        if (!is_resource($this->fp)) {
            return;
        }
        $now = time();
        $tag = 'tag';
        if (isset($context['tag'])) {
            $tag = $context['tag'];
        }
        $message = sprintf(
            '%s % 2d %s %s [%s] %s:%s',
            date('M', $now),
            date('j', $now),
            date('H:i:s', $now),
            (string)$this->localhostName,
            Core_Lib_Logger::$aLevels[$level],
            $tag,
            Core_Lib_Logger::replaceContext((string)$message, $context)
        );
        $message .= "\n"; // for file log only
        $len = strlen($message);
        $wrote = fwrite($this->fp, $message, $len); // @todo 互斥锁
    }
}