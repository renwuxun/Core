<?php


/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/12/29 0029
 * Time: 18:08
 */
class Core_Console_Helper {

    /**
     * 使用：
     * Chexing_Lib_Helper::parseArgs(
     *     array(
     *         '-H'=>array('host', '127.0.0.1', 'this is host'),
     *         '-P'=>array('port', 9527, 'this is port'),
     *         array('infile', 'default-in-file', 'the input file name'),
     *         array('outfile', 'default-out-file', 'the output file name')
     *     )
     * );
     * var_dump($port,$host,$infile,$outfile);
     * @param array $setting
     */
    public static function parseArgs($setting) {
        global $argv;

        $_argv = array();
        $OptMaxWidth = 0;
        $ArgMaxWidth = 0;

        // set default
        foreach ($setting as $key=>$item) { // $setting = array('-H'=>array('host', '127.0.0.1', 'tips'));
            $GLOBALS[$item[0]] = $item[1];
            if (is_string($key)) {
                if (strlen($item[1]) > $OptMaxWidth) {
                    $OptMaxWidth = strlen($item[1]);
                }
            } else {
                if (strlen($item[0]) > $ArgMaxWidth) {
                    $ArgMaxWidth = strlen($item[0]);
                }
            }
        }

        foreach ($argv as $i=>$_v) {
            if ($i==0) {
                continue;
            }

            $k = substr($_v, 0, 2);
            $v = ltrim(substr($_v, 2), ":=");
            if ($k{0} == '-') {
                if (isset($setting[$k])) {
                    if (!settype($v, gettype($setting[$k][1]))) {
                        fwrite(STDERR, $k.' variable type error'.PHP_EOL);
                        self::showHelp($setting, $OptMaxWidth, $ArgMaxWidth);
                        exit(1);
                    }
                    $GLOBALS[$setting[$k][0]] = $v;
                } else {
                    fwrite(STDERR, 'Error: unknown option "'.$k.'"'.PHP_EOL);
                    self::showHelp($setting, $OptMaxWidth, $ArgMaxWidth);
                    exit(1);
                }
            } else {
                $_argv[] = $_v;
            }
        }

        foreach ($_argv as $i=>$item) {
            $setting[$i];
            if (isset($setting[$i])) {
                if (!settype($item, gettype($setting[$i][1]))) {
                    fwrite(STDERR, $item[0].' variable type error'.PHP_EOL);
                    self::showHelp($setting, $OptMaxWidth, $ArgMaxWidth);
                    exit(1);
                }
                $GLOBALS[$setting[$i][0]] = $item;
            }
        }
    }

    private static function showHelp($setting, $OptMaxWidth, $ArgMaxWidth) {
        fwrite(STDERR, 'Usage: '.$GLOBALS['argv'][0].' [options]');
        foreach ($setting as $key => $item) {
            if (!is_string($key)) {
                fwrite(STDERR, ' '.$item[0]);
            }
        }
        fwrite(STDERR, PHP_EOL);

        foreach ($setting as $key => $item) {
            if (!is_string($key)) {
                fwrite(STDERR, '    '.sprintf("%-' ".$ArgMaxWidth."s [set \${$item[0]}] {$item[2]}", $item[0]).PHP_EOL);
            }
        }

        fwrite(STDERR, 'Options:'.PHP_EOL);
        foreach ($setting as $key => $item) {
            if (is_string($key)) {
                $defaultval = sprintf("%-' ".$OptMaxWidth."s", $item[1]);
                fwrite(STDERR, '    '.$key.'='.$defaultval.' [set $'.$item[0].'] '.$item[2].PHP_EOL);
            }
        }
    }
}