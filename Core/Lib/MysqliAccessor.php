<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:34
 */


class Core_Lib_MysqliAccessor extends Core_Lib_DataAccessor {

    const OP_LIKE = 'like';

    /**
     * @var mysqli
     */
    protected $conn = null;
    protected $lastSql = '';


    /**
     * MysqliAccessor constructor.
     * @param string $modelName
     * @param mysqli $conn
     * @throws Exception
     */
    public function __construct($modelName, $conn = null) {
        parent::__construct($modelName);

        if ($conn) {
            $this->conn = $conn;
            return;
        }

        $conf = Core_Lib_App::app()->getConfig()->get('modelServers.'.$modelName);
        if ($conf['sid'] > 0) {
            $ipport = Core_Helper_L5::getInstance()->route($conf['sid']);
            if (!empty($ipport) && $ipport[1] != '0') {
                $conf['host'] = $ipport[0];
                $conf['port'] = $ipport[1];
            }
        }
        $this->conn = new mysqli($conf['host'], $conf['user'], $conf['psw'], $conf['dbname'], $conf['port']);
        if ($this->conn->connect_errno != 0) {
            throw new Exception('model server connect error: '.$this->conn->connect_error);
        }
        if (isset($conf['charset'])) {
            $this->conn->set_charset($conf['charset']);
        }
    }

    public function find() {
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->modelName;

        if ($this->key == '') {
            throw new Exception($modelName . '::' . $modelName::keyField() . ' must be set by filter()');
        }

        $fields = $this->prepareFields();
        $where = $this->prepareWhere();
        $orderBy = $this->prepareOrderBy();
        $limit = $this->prepareLimit();

        $this->lastSql = "SELECT {$fields} FROM `{$modelName::table()}` {$where} {$orderBy} {$limit}";

        $objs = array();
        if ($this->conn->real_query($this->lastSql)) {
            if ($this->conn->field_count) {
                $result = $this->conn->store_result();
                while ($row=$result->fetch_assoc()) {
                    $o = $this->newDataObject();
                    foreach($row as $k=>$v) {
                        $o->$k = $o->srcData[$k] = $v;
                    }
                    $objs[] = $o;
                }
                $result->free();
            }
        }

        return $objs;
    }

    /**
     * @return int -1:error
     * @throws Exception
     */
    public function update() {
        if (empty($this->setFields)) {
            return false;
        }

        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->modelName;

        if($this->key == ''){
            throw new Exception($modelName . '::' . $modelName::keyField() . ' must be set by filter()');
        }

        $setData = $this->prepareSetdata();
        $where = $this->prepareWhere();
        $limit = $this->prepareLimit();

        $this->lastSql = "UPDATE `{$modelName::table()}` SET " . $setData . " $where $limit";
        if (!$this->conn->real_query($this->lastSql)) {
            return -1;
        }
        return $this->conn->affected_rows;
    }

    /**
     * @param int $ignore
     * @return int -1:error
     * @return int
     * @throws Exception
     */
    public function insert($ignore = 1) {
        if (empty($this->setFields)) {
            return -1;
        }

        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->modelName;

        if($this->key == ''){
            throw new Exception($modelName . '::' . $modelName::keyField() . ' must be set by filter()');
        }

        $ignore = $ignore ? 'IGNORE' : '';

        $this->lastSql = "INSERT $ignore INTO `{$modelName::table()}` (`" . implode('`,`', array_keys($this->setFields)) . "`) VALUES ('" . implode("','", array_map('addslashes', $this->setFields)) . "')";
        $this->setFields = array();//reset
        if (!$this->conn->real_query($this->lastSql)) {
            return -1;
        }

        return $this->conn->affected_rows;
    }

    /**
     * @return int -1:error
     * @throws Exception
     */
    public function delete() {
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->modelName;

        if($this->key == ''){
            throw new Exception($modelName . '::' . $modelName::keyField() . ' must be set by filter()');
        }

        $where = $this->prepareWhere();
        $limit = $this->prepareLimit();

        $this->lastSql = "DELETE FROM `{$modelName::table()}` {$where} {$limit}";
        if (!$this->conn->real_query($this->lastSql)) {
            return -1;
        }
        return $this->conn->affected_rows;
    }

    /**
     * @return int <0:error
     * @return int
     * @throws Exception
     */
    public function count() {
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->modelName;

        if($this->key == ''){
            throw new Exception($modelName . '::' . $modelName::keyField() . ' must be set by filter()');
        }

        $fields = 'count(*) as cnt';

        $where = $this->prepareWhere();

        $this->lastSql = "SELECT {$fields} FROM `{$modelName::table()}` {$where}";

        if (!$this->conn->real_query($this->lastSql)) {
            return -1;
        }
        if (!$this->conn->field_count) {
            return -2;
        }
        $result = $this->conn->store_result();
        $row = $result->fetch_assoc();
        $result->free();
        return $row['cnt'];
    }

    protected function prepareSetdata() {
        $data = array();
        foreach ($this->setFields as $k => $v) {
            $v = addslashes($v);
            $data[] = "`$k`='$v'";
        }
        $this->setFields = array();//reset
        return implode(',', $data);
    }

    protected function prepareFields() {
        $fields = '*';
        if (!empty($this->loadFields)) {
            $fields = '`' . implode('`,`', $this->loadFields) . '`';
        }
        $this->loadFields = array();//reset
        return $fields;
    }

    protected function prepareWhere() {
        $where = '';
        if (!empty($this->filterOps)) {
            foreach ($this->filterOps as $k => $op) {
                if ($where != '') {
                    $where .= ' AND ';
                }
                switch($op) {
                    case self::OP_IN:
                        $where .= "`{$this->filterKeys[$k]}` IN ('" . implode("','", array_map('addslashes', $this->filterVals[$k])) . "')";
                        break;
                    case self::OP_LIKE:
                        $where .= "`{$this->filterKeys[$k]}` like '" . addslashes($this->filterVals[$k]) . "%'";
                        break;
                    default:
                        $where .= "`{$this->filterKeys[$k]}` $op '" . addslashes($this->filterVals[$k]) . "'";
                }
            }
        }
        $this->filterKeys = $this->filterOps = $this->filterVals = array();//reset
        if ($where != '') {
            $where = 'WHERE ' . $where;
        }
        return $where;
    }

    protected function prepareOrderBy() {
        $orderBy = '';
        if (!empty($this->sorts)) {
            foreach ($this->sorts as $k => $v) {
                $orderBy .= "`$k` $v,";
            }
            $orderBy = rtrim($orderBy, ',');
        }
        $this->sorts = array();//reset
        return $orderBy;
    }

    protected function prepareLimit() {
        if ($this->limit<1) {
            $this->limit = static::DEFAULT_LIMIT;
        }
        if ($this->offset == 0 ) {
            $limit = "LIMIT {$this->limit}";
        }else{
            $limit = "LIMIT {$this->offset},{$this->limit}";
        }
        $this->offset = 0;//reset
        $this->limit = static::DEFAULT_LIMIT;//reset
        return $limit;
    }

    /**
     * @return int|string primary key
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * @return string
     */
    public function getLastSql() {
        return $this->lastSql;
    }

    protected function halt($msg) {
        throw new Exception($msg, -1);
    }
}
