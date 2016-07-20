<?php


interface Core_Interceptor_IPermission {
    /**
     * @return string
     */
    static function getNoPermissionAction();

    /**
     * @return string[]
     */
    static function getOnlineUserGroups();

    /**
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

        $pathMapGroups = static::pathGroupsMapping();
        if (!isset($pathMapGroups[$path])) {
            return false;
        }

        $needGroups = $pathMapGroups[$path];
        if (empty($needGroups)) {
            return true;
        }

        return !empty(array_intersect(static::getOnlineUserGroups(), $needGroups));
    }
}