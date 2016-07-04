<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:28
 */



abstract class Core_Lib_DataAccessor {

    /**
     * @var static[]
     */
    protected static $instances;

    const OP_GREATER_THAN = '>';
    const OP_GREATER_THAN_OR_EQUAL_TO = '>=';
    const OP_LESS_THAN = '<';
    const OP_LESS_THAN_OR_EQUAL_TO = '<=';
    const OP_EQUAL = '=';
    const OP_NOT_EQUAL_TO = '!=';
    const OP_IN = 'in';
    const SORT_TYPE_DESC = 'desc';
    const SORT_TYPE_ASC = 'asc';

    protected $modelName = '';
    protected $mapping = array();
    protected $filterKeys = array();
    protected $filterOps = array();
    protected $filterVals = array();
    protected $key = ''; // sharding value

    protected $setFields = array();

    protected $loadFields = array();

    protected $sorts = array();

    protected $offset = 0;
    protected $limit = 1000;
    const DEFAULT_LIMIT = 1000;

    protected $memo = array();

    protected $errno = 0;
    protected $errstr = '';

    public function __construct($modelName) {
        $this->modelName = $modelName;
    }

    protected function resetFilter() {
        $this->filterKeys = array();
        $this->filterOps = array();
        $this->filterVals = array();
        $this->setFields = array();
        $this->loadFields = array();
        $this->sorts = array();
        $this->offset = 0;
        $this->limit = self::DEFAULT_LIMIT;
    }

    public function filter($key, $val) {

        return $this->filterByOp($key, is_array($val) ? self::OP_IN : self::OP_EQUAL, $val);
    }

    /**
     * @param $key
     * @param $op
     * @param $val
     * @return $this
     * @throws Exception
     */
    public function filterByOp($key, $op, $val) {
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->getModelName();
        if ($key == $modelName::keyField()) {
            if ($op != self::OP_EQUAL) {
                throw new Exception('key字段只支持=号过滤');
            }
            $this->key = $val;
        }

        $i = array_search($key, $this->filterKeys);
        if ($i!==false && $op==$this->filterOps[$i]) {
            $this->filterVals[$i] = $val;
        }else{
            $this->filterKeys[] = $key;
            $this->filterOps[] = $op;
            $this->filterVals[] = $val;
        }

        return $this;
    }

    /**
     * @return Core_Lib_DataObject
     */
    public function newDataObject(){
        /**
         * @var Core_Lib_DataObject $o
         */
        $o = new $this->modelName;
        $o->setCreateByDataAccessor();
        return $o;
    }

    public function loadField($fields) {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->getModelName();
        $this->loadFields = array_intersect(array_keys($modelName::fieldType()), $fields);

        return $this;
    }

    protected function getModelName() {
        if (''==$this->modelName) {
            $classname = get_class($this);
            throw new Exception($classname.'::modelName must init in '.$classname.'::__construct', -1);
        }
        return $this->modelName;
    }

    /**
     * @param $key
     * @param int $type
     * @return $this
     */
    public function sort($key, $type = SORT_DESC) {
        $this->sorts[$key] = $type;

        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;

        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function setField($key, $val) {
        /**
         * @var $modelName Core_Lib_DataObject
         */
        $modelName = $this->getModelName();
        $fieldType = $modelName::fieldType();
        if (isset($fieldType[$key])) {
            $this->setFields[$key] = $val;
            if ($key == $modelName::keyField()) {
                $this->key = $val;
            }
        }
        return $this;
    }

    /**
     * @param array $memo
     */
    public function setMemo($memo) {
        $this->memo = $memo;
    }

    public function findOne() {
        $this->limit = 1;
        $r = $this->find();
        if (is_array($r) && !empty($r)) {
            return $r[0];
        }
        return null;
    }

    /**
     * @return Core_Lib_DataObject[]
     */
    abstract public function find();

    /**
     * @return bool
     */
    abstract public function update();

    /**
     * @return bool
     */
    abstract public function insert();

    /**
     * @return bool
     */
    abstract public function delete();

    /**
     * @return int
     */
    abstract public function count();

    /**
     * @return array primary keys
     */
    abstract public function lastInsertId();

    /**
     * @return int
     */
    public function getErrno() {
        return $this->errno;
    }

    /**
     * @return string
     */
    public function getErrstr() {
        return $this->errstr;
    }

}