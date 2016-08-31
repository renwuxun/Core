<?php

/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/8/31 0031
 * Time: 17:59
 */
class Core_Helper_AES {

    public static function encode($str, $key) {
        $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB, $iv);
    }

    public static function decode($str, $key) {
        $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB, $iv), "\0");
    }
}