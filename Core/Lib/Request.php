<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 18:37
 */



class Core_Lib_Request {

    private $uri;
    private $path;

    private $GET = [];
    private $POST = [];
    private $COOKIE = [];
    private $FILES = [];
    private $SERVER = [];
    private $ENV = [];

    public function init(
        $GET = null,
        $POST = null,
        $COOKIE = null,
        $FILES = null,
        $SERVER = null,
        $ENV = null
    ) {
        $this->GET = $GET;
        $this->POST = $POST;
        $this->COOKIE = $COOKIE;
        $this->FILES = $FILES;
        $this->SERVER = $SERVER;
        $this->ENV = $ENV;

        unset($GET,$POST,$COOKIE,$FILES,$SERVER,$ENV);
    }

    /**
     * @return string
     */
    public function getUri() {
        if (null === $this->uri) {
            $this->uri = isset($this->SERVER['REQUEST_URI']) ? $this->SERVER['REQUEST_URI'] : '';
        }
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getPath() {
        if (null === $this->path) {
            $qeury = parse_url($this->getUri());
            $this->path = '/'.trim($qeury['path'], '/');
        }
        return $this->path;
    }

    /**
     * @return string
     */
    public function getLastModified() {
        return isset($this->SERVER['HTTP_IF_MODIFIED_SINCE']) ? $this->SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
    }

    /**
     * @param int $lastModifiedAt
     * @return bool
     */
    public function lastModifiedAt($lastModifiedAt) {
        return $this->getLastModified() == gmdate('D, d M Y H:i:s \G\M\T', $lastModifiedAt);
    }

    /**
     * @return string
     */
    public function getEtag() {
        return isset($this->SERVER['HTTP_IF_NONE_MATCH']) ? $this->SERVER['HTTP_IF_NONE_MATCH'] : '';
    }

    /**
     * @param string $etag
     * @return bool
     */
    public function hasEtag($etag) {
        return $this->getEtag() == $etag || $this->getEtag() == '*';
    }

    /**
     * @param int $lastModifiedAt
     * @param string $etag
     * @return bool
     */
    public function clientHasCache($lastModifiedAt, $etag) {
        $hasCache = false;
        if ($lastModifiedAt) {
            $hasCache = $this->lastModifiedAt($lastModifiedAt);
        }
        if ($etag) {
            $hasCache = $this->hasEtag($etag);
        }
        return $hasCache;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param callable $filterCallback trim,strip_tags,addslashes,htmlentities,htmlspecialchars
     * @return mixed
     */
    public function get($key, $default = null, $filterCallback = null) {
        if (!isset($this->GET[$key])) {
            return $default;
        }
        $s = $this->GET[$key];
        if ($filterCallback) {
            $filterCallbacks = explode(',', $filterCallback);
            foreach ($filterCallbacks as $filter) {
                $s = $filter($s);
            }
        }

        return $s;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param callable $filterCallback trim,strip_tags,addslashes,htmlentities,htmlspecialchars
     * @return mixed
     */
    public function post($key, $default = null, $filterCallback = null) {
        if (!isset($this->POST[$key])) {
            return $default;
        }
        $s = $this->POST[$key];
        if ($filterCallback) {
            $filterCallbacks = explode(',', $filterCallback);
            foreach ($filterCallbacks as $filter) {
                $s = $filter($s);
            }
        }

        return $s;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param callable $filterCallback trim,strip_tags,addslashes,htmlentities,htmlspecialchars
     * @return mixed
     */
    public function request($key, $default = null, $filterCallback = null) {
        $val = $this->post($key, null, $filterCallback);
        if ($val === null) {
            return $this->get($key, $default, $filterCallback);
        }
        return $val;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param callable $filterCallback trim,strip_tags,addslashes,htmlentities,htmlspecialchars
     * @return mixed
     */
    public function cookie($key, $default = null, $filterCallback = null) {
        if (!isset($this->COOKIE[$key])) {
            return $default;
        }
        $s = $this->COOKIE[$key];
        if ($filterCallback) {
            $filterCallbacks = explode(',', $filterCallback);
            foreach ($filterCallbacks as $filter) {
                $s = $filter($s);
            }
        }

        return $s;
    }

    /**
     * @return string
     */
    public function getHttpVersion() {
        return substr($this->SERVER['SERVER_PROTOCOL'], 5);
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->SERVER['REQUEST_METHOD'];
    }

    public function getHost(){
        return isset($this->SERVER['HTTP_HOST']) ? $this->SERVER['HTTP_HOST'] : '';
    }

    public function getConnection(){
        return isset($this->SERVER['HTTP_CONNECTION']) ? $this->SERVER['HTTP_CONNECTION'] : '';
    }

    public function getPragma(){
        return isset($this->SERVER['HTTP_PRAGMA']) ? $this->SERVER['HTTP_PRAGMA'] : '';
    }

    public function getCacheControl(){
        return isset($this->SERVER['HTTP_CACHE_CONTROL']) ? $this->SERVER['HTTP_CACHE_CONTROL'] : '';
    }

    public function getAccessControlRequestMethod(){
        return isset($this->SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) ? $this->SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] : '';
    }

    public function getOrigin(){
        return isset($this->SERVER['HTTP_ORIGIN']) ? $this->SERVER['HTTP_ORIGIN'] : '';
    }

    public function getUserAgent(){
        return isset($this->SERVER['HTTP_USER_AGENT']) ? $this->SERVER['HTTP_USER_AGENT'] : '';
    }

    public function getAccessControlRequestHeaders(){
        return isset($this->SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) ? $this->SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] : '';
    }

    public function getAccept(){
        return isset($this->SERVER['HTTP_ACCEPT']) ? $this->SERVER['HTTP_ACCEPT'] : '';
    }

    public function getReferer(){
        return isset($this->SERVER['HTTP_REFERER']) ? $this->SERVER['HTTP_REFERER'] : '';
    }

    public function getAcceptEncoding(){
        return isset($this->SERVER['HTTP_ACCEPT_ENCODING']) ? $this->SERVER['HTTP_ACCEPT_ENCODING'] : '';
    }

    public function getAcceptLanguage(){
        return isset($this->SERVER['HTTP_ACCEPT_LANGUAGE']) ? $this->SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
    }

    /**
     * @param bool $float
     * @return int
     */
    public function getTime($float = false) {
        $k = $float ? 'REQUEST_TIME_FLOAT' : 'REQUEST_TIME';
        return isset($this->SERVER[$k]) ? (int)$this->SERVER[$k] : 0;
    }

    /**
     * @param string $inKey eg: X-Auth-Token
     * @return string
     */
    public function getHttpHeader($inKey) {
        $inKey = strtr($inKey, '-', '_');
        $inKey = strtoupper($inKey);
        $inKey = 'HTTP_'.$inKey;
        return isset($this->SERVER[$inKey]) ? $this->SERVER[$inKey] : '';
    }
}