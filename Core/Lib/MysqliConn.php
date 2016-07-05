<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/4 0004
 * Time: 23:25
 */
abstract class Core_Lib_MysqliConn extends mysqli {

    use Core_Lib_Conn;

    public function __construct($host = null, $port = null, $user = null, $psw = null, $dbname = null, $tbname = null) {
        parent::__construct($host, $user, $psw, $dbname, $port);
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_psw = $psw;
        $this->_dbname = $dbname;
        $this->_tbname = $tbname;
    }

    public function connect($host, $port, $user, $psw, $dbname, $tbname) {
        parent::connect($host, $user, $psw, $dbname, $port);
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_psw = $psw;
        $this->_dbname = $dbname;
        $this->_tbname = $tbname;
    }
}