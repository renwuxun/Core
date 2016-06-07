<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/23 0023
 * Time: 14:52
 */

namespace Core\Lib;


class App {

    /**
     * @var $this
     */
    private static $instance;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var IRoute
     */
    private $route;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var Response
     */
    private $response;

    /**
     * App constructor.
     * @param $config Config
     */
    private function __construct($config) {
        $this->config = $config;
    }

    /**
     * @param $config Config
     * @return App $this
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
     * @return Config
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        if (null === $this->request) {
            $this->request = new Request;
            $this->request->init($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, $_ENV);
        }
        return $this->request;
    }

    /**
     * @return IRoute
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
     * @return Controller
     */
    public function getController() {
        if (null === $this->controller) {
            $controllerName = $this->getRoute()->getControllerName();
            $this->controller = new $controllerName;
        }
        return $this->controller;
    }

    /**
     * @return Response
     */
    public function getResponse() {
        if (null === $this->response) {
            $this->response = new Response;
            $this->response->setHttpVersion($this->getRequest()->getHttpVersion());
        }
        return $this->response;
    }
}