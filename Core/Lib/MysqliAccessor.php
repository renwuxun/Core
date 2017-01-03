<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:34
 */


class Core_Lib_MysqliAccessor extends Core_Lib_DataAccessor {

    const OP_LIKE = 'like';

    protected $lastSql = '';

    public function find() {
//        /**
//         * @var $modelName Core_Lib_DataObject
//         */
//        $modelName = $this->modelName;
//
//        if ($this->shardingValue == '') {
//            throw new Exception($modelName . '::' . $modelName::shardingField() . ' must be set by filter()');
//        }

        $fields = $this->prepareFields();
        $where = $this->prepareWhere();
        $orderBy = $this->prepareOrderBy();
        $limit = $this->prepareLimit();

        $this->lastSql = "SELECT {$fields} FROM `{$this->conn->getTbname()}` {$where} {$orderBy} {$limit}";

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

//        /**
//         * @var $modelName Core_Lib_DataObject
//         */
//        $modelName = $this->modelName;
//
//        if($this->shardingValue == ''){
//            throw new Exception($modelName . '::' . $modelName::shardingField() . ' must be set by filter()');
//        }

        $setData = $this->prepareSetdata();
        $where = $this->prepareWhere();
        $limit = $this->prepareLimit();

        $this->lastSql = "UPDATE `{$this->conn->getTbname()}` SET " . $setData . " $where $limit";
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

//        /**
//         * @var $modelName Core_Lib_DataObject
//         */
//        $modelName = $this->modelName;
//
//        if($this->shardingValue == ''){
//            throw new Exception($modelName . '::' . $modelName::shardingField() . ' must be set by filter()');
//        }

        $ignore = $ignore ? 'IGNORE' : '';

        $this->lastSql = "INSERT $ignore INTO `{$this->conn->getTbname()}` (`" . implode('`,`', array_keys($this->setFields)) . "`) VALUES ('" . implode("','", array_map('addslashes', $this->setFields)) . "')";
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
//        /**
//         * @var $modelName Core_Lib_DataObject
//         */
//        $modelName = $this->modelName;
//
//        if($this->shardingValue == ''){
//            throw new Exception($modelName . '::' . $modelName::shardingField() . ' must be set by filter()');
//        }

        $where = $this->prepareWhere();
        $limit = $this->prepareLimit();

        $this->lastSql = "DELETE FROM `{$this->conn->getTbname()}` {$where} {$limit}";
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
//        /**
//         * @var $modelName Core_Lib_DataObject
//         */
//        $modelName = $this->modelName;
//
//        if($this->shardingValue == ''){
//            throw new Exception($modelName . '::' . $modelName::shardingField() . ' must be set by filter()');
//        }

        $fields = 'count(*) as cnt';

        $where = $this->prepareWhere();

        $this->lastSql = "SELECT {$fields} FROM `{$this->conn->getTbname()}` {$where}";

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
        $modelName = $this->modelName;
        $fields = implode(',',array_map(function($v){return "`$v`";},array_keys($modelName::fieldType())));
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
        if ($orderBy!='') {
            $orderBy = 'ORDER BY '.$orderBy;
        }
        $this->sorts = array();//reset
        return $orderBy;
    }

    protected function prepareLimit() {
        if ($this->offset == 0 ) {
            $limit = "LIMIT {$this->limit}";
        }else{
            $limit = "LIMIT {$this->offset},{$this->limit}";
        }
        $this->offset = 0;//reset
        $this->limit = 1000;//reset
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
        throw new Exception($msg);
    }
}
