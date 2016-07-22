<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/5 0005
 * Time: 0:11
 */
trait Core_Lib_Conn {

    /**
     * @var string
     */
    protected $_host;
    /**
     * @var int
     */
    protected $_port;
    /**
     * @var string
     */
    protected $_user;
    /**
     * @var string
     */
    protected $_psw;
    /**
     * @var string
     */
    protected $_dbname;
    /**
     * @var string
     */
    protected $_tbname;

    /**
     * @var string unix domain socket
     */
    protected $_socket;

    /**
     * @return string
     */
    public function getHost() {
        return $this->_host;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->_port;
    }

    /**
     * @return string
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * @return string
     */
    public function getPsw() {
        return $this->_psw;
    }

    /**
     * @return string
     */
    public function getDbname() {
        return $this->_dbname;
    }

    /**
     * @return string
     */
    public function getTbname() {
        return $this->_tbname;
    }

    /**
     * @return string
     */
    public function getSocket() {
        return $this->_socket;
    }
}