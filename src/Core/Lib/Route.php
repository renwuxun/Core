<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 17:34
 */


class Core_Lib_Route implements Core_Lib_IRoute{

    protected $controllerName = 'Core_Controller_Welcome';
    protected $actionName = 'index';
    protected $args = array();

    public function __construct() {
        $pathInfo = Core_Lib_App::app()->getRequest()->getPathInfo();
        if (isset($pathInfo{1})) {
            $slice = array_filter(explode('/', trim($pathInfo, '/')));
            if (sizeof($slice) > 0) {
                $this->controllerName = basename(PROJECT_PATH).'_Controller_'.ucfirst(array_shift($slice));
            }
            if (sizeof($slice) > 0) {
                $this->actionName = array_shift($slice);
            }
            if (sizeof($slice) > 0) {
                $this->args = $slice;
            }
        }

        if (!is_callable(array($this->controllerName, $this->actionName.'Action'))) {
            $this->actionName = 'index';
            $this->controllerName = Core_Lib_App::app()->getConfig()->get('404Controller');
        }
    }

    /**
     * @return string
     */
    public function getControllerName() {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getActionName() {
        return $this->actionName;
    }

    /**
     * @return array
     */
    public function getArgs() {
        return $this->args;
    }

}