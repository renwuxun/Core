<?php



class Core_Helper_Net_Http {

    const MAX_LINE_SIZE = 8192;

    protected function __construct() {}

    /**
     * @param Core_Helper_Net_Tcp $tcp
     * @param int $timeoutsec
     * @return string
     */
    public static function readHeader($tcp, $timeoutsec = 2) {
        $header = '';

        do {
            $line = $tcp->fgets(self::MAX_LINE_SIZE, $timeoutsec);
            $header .= $line;
            if ($line == "\r\n") {
                break;
            }
            if ($tcp->getErrno() != 0) {
                break;
            }
        } while (!$tcp->feof());

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
            $errno = $tcp->getErrno();
            $errstr = $tcp->getErrstr();
        }elseif(preg_match('/Transfer-Encoding:\s*chunked/is', $header)) {
            do {
                $_chunk_size = intval(hexdec($tcp->fgets(self::MAX_LINE_SIZE, $timeoutsec)));
                if ($tcp->getErrno() != 0) {
                    break;
                }
                if ($_chunk_size > 0) {
                    $body .= $tcp->recv($_chunk_size, $timeoutsec);
                    if ($tcp->getErrno() != 0) {
                        break;
                    }
                }
                $tcp->recv(2, $timeoutsec); // skip \r\n
                if ($tcp->getErrno() != 0) {
                    break;
                }
                if ($_chunk_size < 1) {
                    break;
                }
            } while (!$tcp->feof());
            $errno = $tcp->getErrno();
            $errstr = $tcp->getErrstr();
        }else{
            $errno = 10;
            $errstr = 'unkown http body, header=['.$header.']';
        }

        if (self::ifServerClosed($header)) {
            $tcp->close();
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
        if ($tcp->getErrno() != 0) {
            $errno = $tcp->getErrno();
            $errstr = $tcp->getErrstr();
            return '';
        }
        $header = self::readHeader($tcp, $timeoutsec);
        if ($tcp->getErrno() != 0) {
            $errno = $tcp->getErrno();
            $errstr = $tcp->getErrstr();
            return $header;
        }
        $body = self::readBody($tcp, $header, $errno, $errstr, $timeoutsec);
        if ($tcp->getErrno() != 0) {
            $errno = $tcp->getErrno();
            $errstr = $tcp->getErrstr();
            return $header.$body;
        }
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

    public static function stripHtml($document) {
        // I didn't use preg eval (//e) since that is only available in PHP 4.0.
        // so, list your entities one by one here. I included some of the
        // more common ones.

        $search = array("'<script[^>]*?>.*?</script>'si", // strip out javascript
            "'<[\/\!]*?[^<>]*?>'si", // strip out html tags
            "'([\r\n])[\s]+'", // strip out white space
            "'&(quot|#34|#034|#x22);'i", // replace html entities
            "'&(amp|#38|#038|#x26);'i", // added hexadecimal values
            "'&(lt|#60|#060|#x3c);'i",
            "'&(gt|#62|#062|#x3e);'i",
            "'&(nbsp|#160|#xa0);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&(reg|#174);'i",
            "'&(deg|#176);'i",
            "'&(#39|#039|#x27);'",
            "'&(euro|#8364);'i", // europe
            "'&a(uml|UML);'", // german
            "'&o(uml|UML);'",
            "'&u(uml|UML);'",
            "'&A(uml|UML);'",
            "'&O(uml|UML);'",
            "'&U(uml|UML);'",
            "'&szlig;'i",
        );
        $replace = array("",
            "",
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            chr(174),
            chr(176),
            chr(39),
            chr(128),
            "ä",
            "ö",
            "ü",
            "Ä",
            "Ö",
            "Ü",
            "ß",
        );

        $text = preg_replace($search, $replace, $document);

        return $text;
    }
}
