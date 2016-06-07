<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/6/2 0002
 * Time: 15:00
 */

namespace Core\Helper;


class Signature {
    
    const SEPARATOR = '`';

    /**
     * @param string $data
     * @param int $begin time begin
     * @param int $end time end
     * @param string $salt
     * @return string
     */
    public static function generate($data, $begin, $end, $salt) {
        return md5($data . self::SEPARATOR . $begin . self::SEPARATOR . $end . self::SEPARATOR . $salt);
    }

    /**
     * @param string $data
     * @param int $begin time begin
     * @param int $end time end
     * @param string $salt
     * @return string
     */
    public static function attach($data, $begin, $end, $salt) {
        return self::generate($data, $begin, $end, $salt) . $begin . $end . $data;
    }

    /**
     * @param string $data
     * @param string $salt
     * @param int $now
     * @return false|string
     */
    public static function detach($data, $salt, $now = null) {
        if (!isset($data{52})) {
            return false;
        }

        $now || $now = time();
        $signature = substr($data, 0, 32);
        $begin = substr($data, 32, 10);
        $end = substr($data, 42, 10);
        $data = substr($data, 52);

        if ($signature != self::generate($data, $begin, $end, $salt)) {
            return false;
        }

        if ($begin == $end || ($begin <= $now && $now <= $end)) {
            return $data;
        } else {
            return false;
        }
    }

}