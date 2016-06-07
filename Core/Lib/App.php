<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 14:52
 */



class Core_Lib_App {

    /**
     * @var $this
     */
    private static $instance;

    /**
     * @var Core_Lib_Config
     */
    private $config;

    /**
     * @var Core_Lib_Request
     */
    private $request;

    /**
     * @var Core_Lib_IRoute
     */
    private $route;

    /**
     * @var Core_Lib_Controller
     */
    private $controller;

    /**
     * @var Core_Lib_Response
     */
    private $response;

    /**
     * App constructor.
     * @param $config Core_Lib_Config
     */
    private function __construct($config) {
        $this->config = $config;
    }

    /**
     * @param $config Core_Lib_Config
     * @return $this
     */
    public static function createApp($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function run() {
        $this->getResponse()->setContent(
            $this->getController()->run(
                $this->getRoute()->getActionName(),
                $this->getRoute()->getArgs()
            )
        );
        $this->getResponse()->send();
    }

    /**
     * @return $this
     */
    public static function app() {
        return self::$instance;
    }

    /**
     * @return Core_Lib_Config
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return Core_Lib_Request
     */
    public function getRequest() {
        if (null === $this->request) {
            $this->request = new Core_Lib_Request;
            $this->request->init($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, $_ENV);
        }
        return $this->request;
    }

    /**
     * @return Core_Lib_IRoute
     */
    public function getRoute() {
        if (null === $this->route) {
            $routeName = $this->getConfig()->get('routeName');
            $this->route = new $routeName;
            $this->route->init($this->getRequest()->getPath());
        }
        return $this->route;
    }

    /**
     * @return Core_Lib_Controller
     */
    public function getController() {
        if (null === $this->controller) {
            $controllerName = $this->getRoute()->getControllerName();
            $this->controller = new $controllerName;
        }
        return $this->controller;
    }

    /**
     * @return Core_Lib_Response
     */
    public function getResponse() {
        if (null === $this->response) {
            $this->response = new Core_Lib_Response;
            $this->response->setHttpVersion($this->getRequest()->getHttpVersion());
        }
        return $this->response;
    }
}