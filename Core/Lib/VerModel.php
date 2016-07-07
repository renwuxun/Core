<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/7 0007
 * Time: 15:11
 */
abstract class Core_Lib_VerModel extends Core_Lib_Model {

    public function update() {
        $da = static::dataAccessor();
        $fieldType = static::fieldType();
        foreach(array_keys($fieldType) as $field) {
            if ($this->$field != $this->srcData[$field]) {
                $da->setField($field, $this->$field);
            }
        }
        $da->setField(static::FN_VERSION, $this->version + 1);

        $shardingField = static::shardingField();
        if (null !== $this->$shardingField) {
            $da->filter($shardingField, $this->$shardingField);
        }

        $primaryField = static::primaryField();
        $da->filter($primaryField, $this->$primaryField);
        $da->filter(static::FN_VERSION, $this->version);
        $da->limit(1);
        $r = $da->update();
        if ($r == 1) {
            $this->isLoaded = true;
            $this->version += 1;
            $this->srcData[static::FN_VERSION] = $this->version;
        }

        return $r == 1;
    }
}