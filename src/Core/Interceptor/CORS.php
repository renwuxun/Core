<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/6/13 0013
 * Time: 12:29
 */

/**
 * Class Core_Interceptor_CORS
 * 接受跨域和自定义header
 */
class Core_Interceptor_CORS extends Core_Lib_Interceptor {

    public function before(&$action) {
        $req = Core_Lib_App::app()->getRequest();
        $res = Core_Lib_App::app()->getResponse();

        $origin = $req->getOrigin();
        if ($origin) {
            $res->setHeader('Access-Control-Allow-Origin', $origin);
        }
        if ('OPTIONS' == $req->getMethod()) {
            $acrh = $req->getAccessControlRequestHeaders();
            $res->setHeader('Access-Control-Allow-Headers', $acrh);
            $res->setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
            $res->setHeader('Access-Control-Max-Age', 86400);
            $res->setStatus(204);
            return false;
        }
        return true;
    }

    public function after() {
    }
}