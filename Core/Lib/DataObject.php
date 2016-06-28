<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/24 0024
 * Time: 13:26
 */


interface IDataObject {
    /**
     * @return Core_Lib_DataAccessor
     */
    public static function dataAccessor();

    /**
     * @return string
     */
    public static function table();

    /**
     * @return string
     */
    public static function primaryField();

    /**
     * sharding key
     * 数据分组的依据
     * @return string
     */
    public static function keyField();

    /**
     * @return array
     */
    public static function fieldType();
}



/**
 * Class Core_Lib_DataObject
 */
abstract class Core_Lib_DataObject implements IDataObject {

    const DATA_TYPE_FLOAT='float';
    const DATA_TYPE_INT='int';
    const DATA_TYPE_STR='str';
    const DATA_TYPE_BIN='bin';

    public $srcData = array();

    public function __construct() {
        $fieldType = static::fieldType();
        if (!isset($fieldType[static::primaryField()])) {
            throw new Exception('fieldType返回的数组必须包含主键字段['.static::primaryField().']');
        }
        foreach(array_keys($fieldType) as $field) {
            $this->$field = null;
        }
    }

    /**
     * @return bool
     */
    public function insert() {
        $da = static::dataAccessor();
        $fieldType = static::fieldType();
        foreach(array_keys($fieldType) as $field) {
            if (null !== $this->$field) {
                $da->setField($field, $this->$field);
                $this->srcData[$field] = $this->$field;
            }
        }
        $ret = $da->insert();
        $primaryField = static::primaryField();
        $this->$primaryField || $this->$primaryField = $this->srcData[$primaryField] = $da->lastInsertId();
        return $ret;
    }

    /**
     * @return bool
     */
    public function update() {
        $da = static::dataAccessor();
        $fieldType = static::fieldType();
        foreach(array_keys($fieldType) as $field) {
            if ($this->$field != $this->srcData[$field]) {
                $da->setField($field, $this->$field);
            }
        }

        $keyField = static::keyField();
        if (null !== $this->$keyField) {
            $da->filter($keyField, $this->$keyField);
        }

        $primaryField = static::primaryField();
        $da->filter($primaryField, $this->$primaryField);
        return $da->update();
    }

    public function toArray() {
        $arr = array();
        foreach(array_keys(static::fieldType()) as $field) {
            $arr[$field] = $this->$field;
        }
        return $arr;
    }

    protected $isLoaded = 0;

    public function save() {
        if ($this->isLoaded) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function setCreateByDataAccessor() {
        $this->isLoaded = 1;
    }
}