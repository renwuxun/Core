<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/4 0004
 * Time: 23:25
 */
class Core_Lib_MysqliConn extends mysqli {

    use Core_Lib_Conn;

    public function __construct($host = null, $user = null, $psw = null, $dbname = null, $port = null, $socket = null, $tbname = null) {
        parent::__construct($host, $user, $psw, $dbname, $port, $socket);
        $host!=null && $this->_host = $host;
        $port!=null && $this->_port = $port;
        $user!=null && $this->_user = $user;
        $psw!=null && $this->_psw = $psw;
        $dbname!=null && $this->_dbname = $dbname;
        $tbname!=null && $this->_tbname = $tbname;
        $socket!=null && $this->_socket = $socket;
    }

    public function connect($host = null, $user = null, $psw = null, $dbname = null, $port = null, $socket = null, $tbname = null) {
        parent::connect($host, $user, $psw, $dbname, $port, $socket);
        $host!=null && $this->_host = $host;
        $port!=null && $this->_port = $port;
        $user!=null && $this->_user = $user;
        $psw!=null && $this->_psw = $psw;
        $dbname!=null && $this->_dbname = $dbname;
        $tbname!=null && $this->_tbname = $tbname;
        $socket!=null && $this->_socket = $socket;
    }
}