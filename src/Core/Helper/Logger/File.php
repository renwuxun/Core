<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/9/6 0006
 * Time: 10:51
 */
class Core_Helper_Logger_File extends Core_Lib_Logger {

    protected $fp;
    protected $facility = 1;
    protected $localhostName;

    public function __construct() {
        $this->localhostName = Core_Lib_App::App()->getConfig()->get('appId');
        $this->fp = fopen(static::filePath(), 'a');
    }

    public function __destruct() {
        if (is_resource($this->fp)) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    public static function filePath() {
        return PROJECT_PATH.'/Public/log.txt';
    }

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
            '<%d>%s % 2d %s %s %s:%s',
            $this->facility * 8 + $level,
            date('M', $now),
            date('j', $now),
            date('H:i:s', $now),
            (string)$this->localhostName,
            $tag,
            Core_Lib_Logger::replaceContext((string)$message, $context)
        );
        $message .= "\n"; // for file log only
        $len = strlen($message);
        $wrote = fwrite($this->fp, $message, $len); // @todo 互斥锁
        if ($wrote < $len) {
            $message = substr($message, $wrote);
            $len = $len-$wrote;
            $wrote = fwrite($this->fp, $message, $len);
            if ($wrote < $len) {
                $message = substr($message, $wrote);
                $len = $len-$wrote;
                $wrote = fwrite($this->fp, $message, $len);
                // 事不过三
            }
        }
    }
}