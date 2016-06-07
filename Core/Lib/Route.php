<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 17:34
 */

namespace Core\Lib;


class Route implements IRoute{

    protected $controllerName = 'Core\\Controller\\Welcome';
    protected $actionName = 'index';
    protected $args = [];

    public function init($path) {
        if (isset($path{1})) {
            $slice = array_filter(explode('/', trim($path, '/')));
            if (sizeof($slice) > 0) {
                $this->controllerName = basename(PROJECT_PATH).'\\Controller\\'.ucfirst(array_shift($slice));
            }
            if (sizeof($slice) > 0) {
                $this->actionName = array_shift($slice);
            }
            if (sizeof($slice) > 0) {
                $this->args = $slice;
            }
        }

        if (!is_callable([$this->controllerName, $this->actionName.'Action'])) {
            $this->actionName = 'index';
            $this->controllerName = App::app()->getConfig()->get('404Controller');
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