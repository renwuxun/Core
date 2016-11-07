<?php



class Core_Helper_Net_Http {

	const MAX_LINE_SIZE = 2048;

	protected function __construct() {}

	/**
	 * @param Core_Helper_Net_Tcp $tcp
	 * @param int $timeoutsec
	 * @return string
	 */
	public static function readHeader($tcp, $timeoutsec = 2) {
		$header = '';
		while(1) {
			$line = $tcp->fgets(self::MAX_LINE_SIZE, $timeoutsec);
            if (false === $line) {
                break;
            }
			$header .= $line;
			if ($line=="\r\n") {
				break;
			}
		}

		return $header;
	}

	/**
	 * @param $tcp Core_Helper_Net_Tcp
	 * @param $header
	 * @param $errno
	 * @param $errstr
	 * @param int $timeoutsec
	 * @return string
	 */
	public static function readBody($tcp, $header, &$errno, &$errstr, $timeoutsec = 2) {
		$body = '';
		if (preg_match('/Content-Length:\s*(\d+)/is', $header,$m)){
			$contentlength = intval($m[1]);
			$body = $tcp->recv($contentlength, $timeoutsec);
			if (0 != $tcp->getErrno()) {
				$errno = $tcp->getErrno();
			}
			if ('' != $tcp->getErrstr()) {
				$errstr = $tcp->getErrstr();
			}
		}elseif(preg_match('/Transfer-Encoding:\s*chunked/is', $header)) {
			while(1) {
                $line = $tcp->fgets(self::MAX_LINE_SIZE, $timeoutsec);
                if ($tcp->getErrno() != 0) {
                    $errno = $tcp->getErrno();
                    $errstr = $tcp->getErrstr();
                    break;
                }
				$_chunk_size = intval(hexdec($line));
				if ($_chunk_size>0) {
					$body .= $tcp->recv($_chunk_size, $timeoutsec);
                    if ($tcp->getErrno() != 0) {
                        $errno = $tcp->getErrno();
                        $errstr = $tcp->getErrstr();
                        break;
                    }
				}
				$tcp->recv(2, $timeoutsec);//skip \r\n
                if ($tcp->getErrno() != 0) {
                    $errno = $tcp->getErrno();
                    $errstr = $tcp->getErrstr();
                    break;
                }
				if ($_chunk_size<1) {
					break;
				}
			}
			if (0 != $tcp->getErrno()) {
				$errno = $tcp->getErrno();
			}
			if ('' != $tcp->getErrstr()) {
				$errstr = $tcp->getErrstr();
			}
		}else{
			$errno = -1;
			$errstr = 'unkown http body';
		}

		return $body;
	}

	public static function buildRequest($uri, $data, $method='', $headers=array()) {
		if ($method == '') {
			$method = empty($data) ? 'GET' : 'POST';
		}
		$msg = "$method $uri HTTP/1.1\r\n";
		$queryString = '';
		if (!empty($data)) {
			$queryString = is_array($data) ? http_build_query($data) : $data;
			$msg .= 'Content-Length: '.strlen($queryString)."\r\n";
		}
		foreach($headers as $k=>$v) {
			$msg .= "{$k}: {$v}\r\n";
		}
		$msg .= "\r\n";
		$msg .= $queryString;
		return $msg;
	}

	/**
	 * @param $tcp Core_Helper_Net_Tcp
	 * @param $uri
	 * @param $data
	 * @param $errno
	 * @param $errstr
	 * @param string $method
	 * @param array $headers
	 * @param int $timeoutsec
	 * @return string
	 */
	public static function request($tcp, $uri, $data, &$errno, &$errstr, $method='', $headers=array(), $timeoutsec=2) {
		$msg = self::buildRequest($uri, $data, $method, $headers);
		$tcp->send($msg, $timeoutsec);
		$header = self::readHeader($tcp, $timeoutsec);
		$body = self::readBody($tcp, $header, $errno, $errstr, $timeoutsec);
		return $body;
	}

	/**
	 * 从http响应报头中解析出状态码
	 * @param string $header
	 * @return int
	 */
	public static function getHttpCode($header='') {
		return (int)substr($header,9,3);
	}

	/**
	 * 检查响应中是否包含Connection: close
	 * @param string $header
	 * @return bool
	 */
	public static function ifServerClosed($header='') {
		if (preg_match('/connection:\s*close/is', $header)) {
			return true;
		}
		return false;
	}
}
