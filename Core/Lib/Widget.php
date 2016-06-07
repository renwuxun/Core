<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 23:26
 */



abstract class Core_Lib_Widget extends Core_Lib_Controller {

    protected $cacheHandler;
    protected $cacheExpire = 0;

    protected static function viewPath() {
        return PROJECT_PATH . '/View/Widget';
    }

    /**
     * make cacheable
     * @param string $sAction
     * @param array $args
     * @return string
     */
    public function run($sAction, $args = []) {
        $cacheHandler = $this->getCacheHandler();
        if (null !== $cacheHandler) {
            $cacheKey = $this->getCacheKey();
            $cache = $cacheHandler->get($cacheKey);
            if ($cache != '') {
                return $cache;
            }
            $s = parent::run($sAction, $args);
            if ($s != '') {
                $cacheHandler->set($cacheKey, $s, $this->cacheExpire);
            }
            return $s;
        } else {
            return parent::run($sAction, $args);
        }
    }

    /**
     * @return mixed
     */
    protected function getCacheHandler() {
        return $this->cacheHandler;
    }

    protected function getCacheKey() {
        return get_class($this);
    }
}