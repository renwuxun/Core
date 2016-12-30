<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 16:44
 */




abstract class Core_Lib_Config{

    private $config = array();

    private static function internalConfig() {
        return array();
    }

    private function getConfig() {
        if (empty($this->config)) {
            /**
             * @var $className $this
             */
            for ($className = get_class($this); $className; $className = get_parent_class($className)) {
                $this->config = array_merge($className::config(), $this->config);
            }
            $this->config = array_merge($this->config, self::internalConfig());
        }
        return $this->config;
    }

    /**
     * @param $key string
     * @return null|string|array
     */
    public function get($key) {
        $val = $this->getConfig();
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (isset($val[$k])) {
                $val = $val[$k];
            } else {
                return null;
            }
        }
        return $val;
    }

    protected static function config() {
        return array(
            'appId' => 9527,
            'routeName' => 'Core_Lib_Route',
            '404Controller' => 'Core_Controller_Notfound',
            'UUIDServer' => array('host'=>'127.0.0.1', 'port'=>9527),
            'L5Server' => array('host'=>'127.0.0.1', 'port'=>9528),
            'logger' => 'Core_Helper_Logger_File'
        );
    }
}