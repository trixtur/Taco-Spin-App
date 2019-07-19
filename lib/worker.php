<?php
$GLOBALS['count'] = 0;
$GLOBALS['running'] = false;
final class Worker {
    public static function run() {
        Worker::rotate();
    }

    public static function rotate() {
        $GLOBALS['count'] = 0;
        $GLOBALS['running'] = true;

        while($GLOBALS['running']) {
            $GLOBALS['count'] += 1; // TODO fix.
            sleep(1);
        }
    }

    public static function stop() {
        $GLOBALS['running'] = false;
    }

    public static function report() {
        echos($GLOBALS['count'], 'yellow');
        Main::log(E_NOTICE, 'Current count is ' . $GLOBALS['count']);
        return $GLOBALS['count'];
    }
}
