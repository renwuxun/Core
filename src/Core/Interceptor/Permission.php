<?php


interface Core_Interceptor_IPermission {
    /**
     * 告诉Core框架当用户访问了一个没有访问权限的Action时调用的Action类
     * @return string
     */
    static function getNoPermissionAction();

    /**
     * 告诉Core框架当前在线用户的用户组数组，如:['admin','xxx',...]
     * @return string[]
     */
    static function getOnlineUserGroups();

    /**
     * 告诉Core系统，什么/controller/action只能那些组访问[组为空表示不限制访问]
     * @return array
     */
    static function pathGroupsMapping();
}


/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/7/20 0020
 * Time: 11:24
 */
abstract class Core_Interceptor_Permission extends Core_Lib_Interceptor implements Core_Interceptor_IPermission {

    public function before(&$action) {
        if (!static::groupsCanGoPath(Core_Lib_App::app()->getRequest()->getPath())) {
            Core_Lib_App::app()->getController()->setOuterAction($action, static::getNoPermissionAction());
        }

        return true;
    }

    protected static function groupsCanGoPath($path) {
        $slice = array_filter(explode('/', $path));
        $path = '/';
        if (!empty($slice)) {
            $path .= array_shift($slice);
        }
        if (!empty($slice)) {
            $path .= '/'.array_shift($slice);
        }

        $foundNeedGroups = false;
        $needGroups = array();
        foreach (static::pathGroupsMapping() as $k=>$v) {
            if ('/'.trim($k, '/') == $path) {
                $foundNeedGroups = true;
                $needGroups = $v;
                break;
            }
        }
        if (!$foundNeedGroups) {
            return false;
        }

        if (empty($needGroups)) {
            return true;
        }
        $arr = array_intersect(static::getOnlineUserGroups(), $needGroups);
        return !empty($arr);
    }
}