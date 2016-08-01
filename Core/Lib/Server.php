<?php


/*
//usage:

Core_Lib_Server::init();

function sayhi($workerId) {
    sleep(1);
    echo posix_getpid().",$workerId say hi\n";
}

Core_Lib_Server::run('sayhi', [], 10, 2);

*/


/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/8/1 0001
 * Time: 18:31
 */
class Core_Lib_Server {
    const RESTART_EXIT_CODE = 2;
    const SIGNAL_EXIT_CODE = 3;

    private static $workerPids = [];
    private static $childStop = 0;
    private static $childExitCode = self::RESTART_EXIT_CODE;

    private static function usage() {
        global $argv;
        echo "usage:\n";
        echo "      php {$argv[0]}\n";
        echo "      php {$argv[0]} daemon\n";
        echo "      php {$argv[0]} daemon stdout.log\n";
        echo "      php {$argv[0]} daemon stdout.log stderr.log\n";
        echo "      php {$argv[0]} stop\n";
    }

    public static function onShutdown($pidfile) {
        if (!file_exists($pidfile)) {
            return;
        }
        if (posix_getpid() == (int)file_get_contents($pidfile)) {
            unlink($pidfile);
        }
    }

    public static function init() {
        global $argv, $STDIN, $STDOUT, $STDERR;


        if (php_sapi_name() != 'cli') {
            die("只支持cli方式运行\n");
        }


        $scriptRealpath = realpath($argv[0]);
        /*if ($scriptRealpath == __FILE__) {
            die('不支持直接运行:'.__FILE__."\n");
        }*/


        $pidfile = $scriptRealpath.'.pid';


        if (!isset($argv[1])) {
            $argv[1] = 'start';
        }
        switch ($argv[1]) {
            case 'start':
                if (file_exists($pidfile) && posix_kill((int)file_get_contents($pidfile), 0)) {
                    echo "{$argv[0]} is running\n";
                    die();
                }
                break;
            case 'daemon':
                if (file_exists($pidfile) && posix_kill((int)file_get_contents($pidfile), 0)) {
                    echo "{$argv[0]} is running\n";
                    die();
                }
                /**
                 * @see http://blog.csdn.net/tengzhaorong/article/details/9764655
                 */
                umask(0);
                switch (pcntl_fork()) {
                    case -1:
                        die("pcntl_fork() error\n");
                        break;
                    case 0://child
                        posix_setsid();
                        switch (pcntl_fork()) {
                            case -1:
                                die("pcntl_fork() error\n");
                                break;
                            case 0://child child
                                //chdir("/");
                                fclose(STDIN);
                                fclose(STDOUT);
                                fclose(STDERR);
                                $STDIN = fopen('/dev/null', 'r');
                                $stdoutFile = isset($argv[2]) ? $argv[2] : $scriptRealpath.'.log';
                                $stderrFile = isset($argv[3]) ? $argv[3] : $scriptRealpath.'.err';
                                $STDOUT = fopen($stdoutFile, 'a');
                                $STDERR = fopen($stderrFile, 'a');
                                break;
                            default: //parent
                                die();
                        }
                        break;
                    default: //parent
                        die();
                }
                break;
            case 'stop':
                if (!file_exists($pidfile) || !posix_kill((int)file_get_contents($pidfile), 0)) {
                    echo "{$argv[0]} is not running\n";
                } else {
                    posix_kill((int)file_get_contents($pidfile), SIGQUIT);
                    echo "{$argv[0]} stoped\n";
                }
                die();
                break;
            default:
                self::usage();
                die();
        }


        file_put_contents($pidfile, posix_getpid());
        register_shutdown_function([__CLASS__,'onShutdown'], $pidfile);
    }

    /**
     * @param callable $callback
     * @param array $args
     * @param int $maxLoop
     * @param int $workerNum
     */
    public static function run($callback, $args = [], $maxLoop = 1000, $workerNum = 1) {
        cli_set_process_title($GLOBALS['argv'][0].' master');

        pcntl_signal(SIGQUIT, array(__CLASS__, 'killWorkers'));
        pcntl_signal(SIGTERM, array(__CLASS__, 'killWorkers'));
        pcntl_signal(SIGHUP, array(__CLASS__, 'killWorkers'));
        pcntl_signal(SIGINT, array(__CLASS__, 'killWorkers'));

        for($i=0; $i<$workerNum; $i++) {
            $cpid = self::spawnChild($i, $callback, $args, $maxLoop);
            if ($cpid < 1) {
                echo date('Y-m-d H:i:s ')."can not spawn a child\n";
                return;
            }
            self::$workerPids[] = $cpid;
        }


        while(1) {
            pcntl_signal_dispatch();
            $old_cpid = pcntl_waitpid(-1, $status, WNOHANG);
            if (0 < $old_cpid) {
                $exitCode = pcntl_wexitstatus($status);
                if ($exitCode == self::SIGNAL_EXIT_CODE) {
                    echo date('Y-m-d H:i:s ')."worker[$old_cpid] exit\n";
                    foreach (self::$workerPids as $k=>$v) {
                        if ($old_cpid == $v) {
                            unset(self::$workerPids[$k]);
                            break;
                        }
                    }
                    if (empty(self::$workerPids)) {
                        break;
                    }
                } else {
                    $k = array_search($old_cpid, self::$workerPids);

                    if ($exitCode != self::RESTART_EXIT_CODE) {
                        echo date('Y-m-d H:i:s ')."worker:{$k},pid:{$old_cpid}意外退出\n";
                    }

                    $cpid = self::spawnChild($k, $callback, $args, $maxLoop);
                    if ($cpid < 1) {
                        echo date('Y-m-d H:i:s ')."can not spawn a child\n";
                        break;
                    }
                    self::$workerPids[$k] = $cpid;

                    if ($exitCode != self::RESTART_EXIT_CODE) {
                        echo date('Y-m-d H:i:s ')."worker:{$k},pid:{$cpid}重新拉起\n";
                    }
                }
            } else {
                usleep(100000);
            }
        }


        while(-1 != pcntl_wait($status)) {} // 等待所有的子进程退出
        echo date('Y-m-d H:i:s ')."master[".posix_getpid()."] exit\n";
    }

    /**
     * @param int $workerId
     * @param callable $callback
     * @param array $args
     * @param int $maxLoop
     * @return int
     */
    private static function spawnChild($workerId, $callback, $args = [], $maxLoop = 1000) {
        $childPid = pcntl_fork(); // 一次调用，两次返回
        if ($childPid == 0) { // child
            $args[] = $workerId; // 告诉callback它的固定id
            cli_set_process_title($GLOBALS['argv'][0].' worker');

            pcntl_signal(SIGQUIT, array(__CLASS__, 'quitHandler')); // 覆盖父进程的信号处理器
            pcntl_signal(SIGTERM, array(__CLASS__, 'quitHandler')); // 覆盖父进程的信号处理器
            pcntl_signal(SIGHUP, array(__CLASS__, 'quitHandler')); // 覆盖父进程的信号处理器
            pcntl_signal(SIGINT, array(__CLASS__, 'quitHandler')); // 覆盖父进程的信号处理器

            self::$childStop = 0;
            self::$childExitCode = self::RESTART_EXIT_CODE;

            for($i=0; !self::$childStop && $i<$maxLoop; $i++) {
                if (false === call_user_func_array($callback, $args)) {
                    break;
                }
                pcntl_signal_dispatch();
            }
            exit(self::$childExitCode); //非SIGNAL_EXIT_CODE退出码，表示子进程需要重新拉起
        } else {
            return $childPid;
        }
    }

    private static function quitHandler() {
        self::$childStop = 1;
        self::$childExitCode = self::SIGNAL_EXIT_CODE;
    }

    private static function killWorkers() {
        foreach (self::$workerPids as $cpid) {
            posix_kill($cpid, SIGQUIT);
        }
    }
}