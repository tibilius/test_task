<?php


namespace App;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class SimpleLoggerFactory
 * for prod monologbundle should be used here
 * @package App
 */
class SimpleLoggerFactory
{
    protected static $loggers = [];

    public static function createFileLogger($path, $name = 'default')
    {
        if (isset(self::$loggers[$name])) {
            return self::$loggers[$name];
        }
        $logger = new Logger($name);
        if (!file_exists($path)) {
            static::createLogFilePath($path);
        }
        $logger->pushHandler(new StreamHandler($path));

        return self::$loggers[$name] = $logger;
    }

    protected static function createLogFilePath($filepath)
    {
        if (file_exists($filepath)) {
            return;
        }
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0644, true);
        }
        file_put_contents($filepath, '');
    }


}