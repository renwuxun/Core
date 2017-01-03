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
     * @var Core_Lib_Logger
     */
    private $logger;

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
     * @throws Exception
     */
    public function getRoute() {
        if (null === $this->route) {
            $routeName = $this->getConfig()->get('routeName');
            $this->route = new $routeName;
            if (!$this->route instanceof Core_Lib_IRoute) {
                throw new Exception($routeName.' need implements Core_Lib_IRoute');
            }
        }
        return $this->route;
    }

    /**
     * @return Core_Lib_Controller
     * @throws Exception
     */
    public function getController() {
        if (null === $this->controller) {
            $controllerName = $this->getRoute()->getControllerName();
            $this->controller = new $controllerName;
            if (!$this->controller instanceof Core_Lib_Controller) {
                throw new Exception($controllerName.' must be subclass of Core_Lib_Controller');
            }
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

    /**
     * @return Core_Lib_Logger
     * @throws Exception
     */
    public function getLogger() {
        if (null === $this->logger) {
            $sLogger = $this->getConfig()->get('logger');
            $this->logger = new $sLogger;
            if (!$this->logger instanceof Core_Lib_Logger) {
                throw new Exception($sLogger.' must be subclass of Core_Lib_Logger');
            }
        }
        return $this->logger;
    }
}