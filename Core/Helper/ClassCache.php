<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/5/25 0025
 * Time: 10:56
 */

namespace Core\Helper;



/*
ClassCache::run(
    PROJECT_PATH . '/Cache/ClassesCached',
    function () {
        App::createApp(new Config())->run();
    }
);
*/

/**
 * Class ClassCache
 * @package Core\Helper
 */
class ClassCache {
    private static $classList = [];
    public static function run($cacheFile, $callback) {
        if (file_exists($cacheFile)) {
            include($cacheFile);
            $callback();
            return;
        }
        self::$classList = array_merge(get_declared_interfaces(), get_declared_classes());
        $callback();
        self::$classList = array_diff(array_merge(get_declared_interfaces(), get_declared_classes()), self::$classList);

        $content = "<?php\n\n";
        foreach (self::$classList as $c) {
            $_content=file_get_contents((new \ReflectionClass($c))->getFileName());
            $pos = strpos($_content, '<?php');
            if (0 === $pos) {
                $_content = substr($_content, 5);
            } elseif (0 === strpos($_content, '<?')) {
                $_content = substr($_content, 2);
            }
            $content .= $_content."\n";
        }
        file_put_contents($cacheFile, $content);
    }
}