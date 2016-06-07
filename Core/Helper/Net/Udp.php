<?php

namespace Core\Helper\Net;


class Udp {

	/**
	 * @var Udp[]
	 */
	protected static $instances=array();

	protected $strHost='';
	protected $iPort=0;
	protected $iTimeout;
	protected $iLastErrNo = 0;
	protected $strLastErr = '';
	protected $fp;

	public static function getInstance($strHost, $iPort, $iTimeout = 1) {
		$key = static::getKey($strHost, $iPort, $iTimeout);
		if (!isset(static::$instances[$key])) {
			static::$instances[$key] = new static($strHost, $iPort, $iTimeout);
		}
		return static::$instances[$key];
	}

	protected static function getKey($strHost, $iPort, $iTimeout) {
		return $strHost.'-'.$iPort.'-'.$iTimeout;
	}

	protected function __construct($strHost, $iPort, $iTimeout = 1){
		$this->strHost = $strHost;
		$this->iPort = $iPort;
		$this->iTimeout = $iTimeout;
	}


	public function connect() {
		$this->fp = fsockopen('udp://' . $this->strHost, $this->iPort, $this->iLastErrNo, $this->strLastErr, $this->iTimeout);
		if (!$this->fp) {
			return false;
		}
		stream_set_timeout($this->fp, $this->iTimeout);
		return true;
	}

	public function send($strSend) {
		$iSendLen = strlen($strSend);
		$ret = fwrite($this->fp, $strSend, $iSendLen);
		if ($ret != $iSendLen) {
			$this->strLastErr = "fwrite failed. ret:[$ret]";
			if (isset($stream_info['timed_out'])) {
				$this->strLastErr .= ' socket_timed_out';
			}
			return false;
		}
		return true;
	}

	public function close() {
		if (is_resource($this->fp)) {
			fclose($this->fp);
			$this->fp = null;
			unset(static::$instances[static::getKey($this->strHost,$this->iPort,$this->iTimeout)]);
		}
	}

	public function isConnected(){
		return is_resource($this->fp);
	}
}