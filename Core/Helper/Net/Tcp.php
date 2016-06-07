<?php



class Core_Helper_Net_Tcp {

	protected $host;
	protected $port;
	protected $fp;

	protected $errno = 0;
	protected $errstr = '';

	public function __construct($host, $port) {
		$this->host = $host;
		$this->port = $port;
	}

	public function __destruct() {
		$this->close();
	}

	public function connect($timeoutsec = 2) {
		/**
		 * http://php.net/manual/zh/function.fsockopen.php
		 * Note:
		 * 注意：如果你要对建立在套接字基础上的读写操作设置操作时间设置连接时限，
		 * 请使用stream_set_timeout()，
		 * fsockopen()的连接时限（timeout）的参数仅仅在套接字连接的时候生效。
		 */
		$this->fp = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $timeoutsec);
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
			stream_set_timeout($this->fp, $timeoutsec);
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
			stream_set_timeout($this->fp, $timeoutsec);
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
		}
		return $str;
	}

	public function fgets($length = null, $timeoutsec = 2) {
		stream_set_timeout($this->fp, $timeoutsec);
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