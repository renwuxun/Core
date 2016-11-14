<?php



class Core_Helper_Net_Tcp {

    protected $fp;

    protected $errno = 0;
    protected $errstr = '';

    public function connect($host, $port, $timeoutsec = 2, $fp = null) {
        $this->errno = 0;
        $this->errstr = '';

        if ($fp && is_resource($fp)) {
            $this->fp = $fp;
            return true;
        }

        /**
         * http://php.net/manual/zh/function.fsockopen.php
         * Note:
         * 注意：如果你要对建立在套接字基础上的读写操作设置操作时间设置连接时限，
         * 请使用stream_set_timeout()，
         * fsockopen()的连接时限（timeout）的参数仅仅在套接字连接的时候生效。
         */
        $this->fp = @fsockopen($host, $port, $this->errno, $this->errstr, $timeoutsec);
        if (false === $this->fp && 0 == $this->errno) {
            $this->errno = 10;
            $this->errstr = 'error before connect()';
        }
        return is_resource($this->fp);
    }

    public function close() {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    public function send($msg, $timeoutsec = 2) {
        $this->errno = 0;
        $this->errstr = '';

        if (@feof($this->fp)) {
            $this->errno = 50;
            $this->errstr = 'end of fp[send]';
            return 0;
        }

        $length = strlen($msg);
        $wrote = 0;

        if (!@stream_set_timeout($this->fp, $timeoutsec)) {
            $errData = error_get_last();
            $this->errno = $errData['type'];
            $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
            return $wrote;
        }

        while ($wrote<$length) {
            $_wrote = @fwrite($this->fp, $msg, $length-$wrote);
            $wrote += $_wrote;
            $msg = substr($msg, $wrote);
            $info = @stream_get_meta_data($this->fp);
            if (isset($info['timed_out']) && $info['timed_out']) {
                $this->errno = 20;
                $this->errstr = 'tcp send timeout';
                break;
            }
            if (!$_wrote) {
                $errData = error_get_last();
                $this->errno = $errData['type'];
                $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
                break;
            }
        }

        return $wrote;
    }

    public function recv($length, $timeoutsec = 2) {
        $this->errno = 0;
        $this->errstr = '';

        if (@feof($this->fp)) {
            $this->errno = 50;
            $this->errstr = 'end of fp[recv]';
            return '';
        }

        $got = 0;
        $str = '';

        if (!@stream_set_timeout($this->fp, $timeoutsec)) {
            $errData = error_get_last();
            $this->errno = $errData['type'];
            $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
            return $str;
        }

        while ($got < $length) {
            $tmp = @fread($this->fp, $length - $got);
            $str .= $tmp;
            $got += strlen($tmp);
            $info = @stream_get_meta_data($this->fp);
            if (isset($info['timed_out']) && $info['timed_out']) {
                $this->errno = 30;
                $this->errstr = 'connection recv timeout';
                break;
            }
            if (isset($info['eof']) && $info['eof']) {
                break;
            }
            if (!$tmp) {
                $errData = error_get_last();
                $this->errno = $errData['type'];
                $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
                break;
            }
        }
        return $str;
    }

    public function fgets($length = null, $timeoutsec = 2) {
        $this->errno = 0;
        $this->errstr = '';

        if (@feof($this->fp)) {
            $this->errno = 50;
            $this->errstr = 'end of fp[fgets]';
            return '';
        }

        if (!@stream_set_timeout($this->fp, $timeoutsec)) {
            $errData = error_get_last();
            $this->errno = $errData['type'];
            $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
            return '';
        }

        $str = @fgets($this->fp, $length);
        $info = @stream_get_meta_data($this->fp);

        if ($this->errno == 0 && isset($info['timed_out']) && $info['timed_out']) {
            $this->errno = 40;
            $this->errstr = 'connection recv[fgets] timeout';
        }

        if ($this->errno == 0 && !$str) {
            $errData = error_get_last();
            $this->errno = $errData['type'];
            $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
        }

        return $str;
    }

    public function feof() {
        return @feof($this->fp);
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