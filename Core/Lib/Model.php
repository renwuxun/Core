<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:49
 */



abstract class Core_Lib_Model extends Core_Lib_DataObject {

    public function __set($name, $value) {
        $m = 'set'.ucfirst($name);
        if (method_exists($this, $m)) {
            $this->$m($value);
        } else {
            throw new Exception('property '.get_class().'::'.$name.' access deny');
        }
    }

    public function __get($name) {
        $m = 'get'.ucfirst($name);
        if (method_exists($this, $m)) {
            $this->$m();
        } else {
            throw new Exception('property '.get_class().'::'.$name.' access deny');
        }
    }

    /**
     * @deprecated 0.3.0
     * @return Core_Lib_Conn
     * @throws Exception
     */
    protected static function getConn() {
        static $conn;
        if (null === $conn) {
            $conf = Core_Lib_App::app()->getConfig()->get('modelServers.'.get_called_class());
            if ($conf['sid'] > 0) {
                $ipport = Core_Helper_L5::getInstance()->route($conf['sid']);
                if (!empty($ipport) && $ipport[1] != '0') {
                    $conf['host'] = $ipport[0];
                    $conf['port'] = $ipport[1];
                }
            }
            $conn = new Core_Lib_MysqliConn($conf['host'], $conf['user'], $conf['psw'], $conf['dbname'], $conf['port'], null, $conf['tbname']);
            if ($conn->connect_errno != 0) {
                throw new Exception('model server connect error: '.$conn->connect_error);
            }
            if (isset($conf['charset'])) {
                $conn->set_charset($conf['charset']);
            }
        }
        return $conn;
    }
}