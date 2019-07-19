<?php
/**
 * @license PHP Daemon
 * http://www.xarg.org/2016/07/how-to-write-a-php-daemon/
 *
 * Copyright (c) 2016, Robert Eisele (robert@xarg.org)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 **/
final class Main {
    // Flag if daemon is still running
    private static $run = true;
    // The screen terminal connected
    public static $screen = null;
    /**
    The actual loop which handles the process execution and sleep cycles
    */
    public static function loop() {
        do {
            echos("BEGINNING\n", "green");
            $load = Worker::run();
            if (DAEMON_FORK) {
                $sleep = MAX_SLEEP + $load * (MIN_SLEEP - MAX_SLEEP);
                
                setproctitle(NAME . ': ' . round(100 * $load, 1) . '%');
                echos("Sleep for "); echos($sleep, "magenta"); echos(" seconds\n\n");
                sleep($sleep);
                
            } else if (0 == $load) {
                break;
            }
            
        } while (self::$run);
        // Close extern ressources
    }
    /**
    Registering the environment
    */
    public static function registerEnv() {
        file_put_contents(DAEMON_PID, getmypid());
        posix_setuid(DAEMON_UID);
        posix_setgid(DAEMON_GID);
        
        self::_openConsole(posix_ttyname(STDOUT));
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
    }
    /**
    Opens the console
    */
    private static function _openConsole($screen) {
        if (!empty($screen) && false !== ($fd = fopen($screen, "c"))) {
            self::$screen = $fd;
        }
    }
    /**
    The signal handler function
    */
    public static function _handleSignal($signo) {
        switch ($signo) {
            /*
             * Attention: The sigterm is only recognized outside a mysqlnd poll()
             */
            case SIGTERM:
                self::log(E_NOTICE, 'Received SIGTERM, dying...');
                self::$run = false;
                Worker::stop();
                posix_kill(posix_getpid(), SIGUSR2);
                return;
            case SIGUSR1:
                // START SPIN
                self::$run = true;
                self::log(E_NOTICE, 'Received SIGSTART, rotate...');
                Worker::rotate();
                return;
            case SIGUSR2:
                self::log(E_NOTICE, 'Received SIGSTIO, stop rotating...');
                self::$run = false;
                Worker::stop();
                // STOP SPIN
                return;
        }
    }
    /**
    Sets up the signal handlers
    */
    public static function registerSignal() {
        pcntl_signal(SIGTERM, 'Main::_handleSignal');
        pcntl_signal(SIGUSR1, 'Main::_handleSignal');
        pcntl_signal(SIGUSR2, 'Main::_handleSignal');
    }
    /**
    The error handler for PHP
    */
    public static function handleError($errno, $errstr, $errfile, $errline, $errctx) {
        if (error_reporting() == 0) {
            return;
        }
        Main::log($errno, $errstr . " on line " . $errline . "(" . $errfile . ") -> " . var_export($errctx, true));
        /* Don't execute PHP's internal error handler */
        return true;
    }
    /**
    The system log function
    */
    public static function log($code, $msg, $var = null) {
        static $codeMap = array(
            E_ERROR   => "Error",
            E_WARNING => "Warning",
            E_NOTICE  => "Notice"
        );
        $msg = date('[d-M-Y H:i:s] ') . $codeMap[$code] . ': ' . $msg;
        if (null !== $var) {
            $msg.= "\n";
            $msg.= var_export($var, true);
            $msg.= "\n";
            $msg.="\n";
        }
        file_put_contents(DAEMON_LOG, $msg . "\n", FILE_APPEND);
    }
}
