<?php



class Core_Helper_Net_Tcp {

    protected $fp;

    protected $errno = 0;
    protected $errstr = '';

    public function connect($host, $port, $timeoutsec = 2, $fp = null) {
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
        $errData = error_get_last();
        if ($errData){
            $this->errno = $errData['type'];
            $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
        }
        return is_resource($this->fp);
    }

    public function close() {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    public function send($msg, $timeoutsec = 2) {
        $length = strlen($msg);
        $wrote = 0;
        while($wrote<$length) {
            if (!@stream_set_timeout($this->fp, $timeoutsec)) {
                $errData = error_get_last();
                if ($this->errno == 0) {
                    $this->errno = $errData['type'];
                }
                if ($this->errstr == '') {
                    $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
                }
                break;
            }
            $wrote += fwrite($this->fp, $msg, $length-$wrote);
            $info = stream_get_meta_data($this->fp);
            if ($info['timed_out']) {
                if ($this->errno == 0) {
                    $this->errno = -1;
                }
                if ($this->errstr == '') {
                    $this->errstr = 'connection send timeout';
                }
                break;
            }
        }
        return $wrote;
    }

    public function recv($length, $timeoutsec = 2) {
        $got = 0;
        $str = '';
        while($got < $length) {
            if (!@stream_set_timeout($this->fp, $timeoutsec)) {
                $errData = error_get_last();
                if ($this->errno == 0) {
                    $this->errno = $errData['type'];
                }
                if ($this->errstr == '') {
                    $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
                }
                break;
            }
            $tmp = fread($this->fp, $length - $got);
            $info = stream_get_meta_data($this->fp);
            if ($info['timed_out']) {
                if ($this->errno == 0) {
                    $this->errno = -1;
                }
                if ($this->errstr == '') {
                    $this->errstr = 'connection recv timeout';
                }
                break;
            }
            $str .= $tmp;
            $got += strlen($tmp);
            if ($info['eof']) {
                break;
            }
        }
        return $str;
    }

    public function fgets($length = null, $timeoutsec = 2) {
        if (!@stream_set_timeout($this->fp, $timeoutsec)) {
            $errData = error_get_last();
            if ($this->errno == 0) {
                $this->errno = $errData['type'];
            }
            if ($this->errstr == '') {
                $this->errstr = $errData['file'].':'.$errData['line'].', '.$errData['message'];
            }
            return '';
        }
        $str = fgets($this->fp, $length);
        $info = stream_get_meta_data($this->fp);
        if ($info['timed_out']) {
            if ($this->errno == 0) {
                $this->errno = -1;
            }
            if ($this->errstr == '') {
                $this->errstr = 'connection recv[fgets] timeout';
            }
        }
        return $str;
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