<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Log class.
 *
 * @method static addAlert($message, array $context = [])
 * @method static addCritical($message, array $context = [])
 * @method static addDebug($message, array $context = [])
 * @method static addEmergency($message, array $context = [])
 * @method static addError($message, array $context = [])
 * @method static addInfo($message, array $context = [])
 * @method static addNotice($message, array $context = [])
 * @method static addRecord($message, array $context = [])
 * @method static addWarning($message, array $context = [])
 * @method static critical($message, array $context = [])
 * @method static emerg($message, array $context = [])
 * @method static debug($message, array $context = []) : boolean
 * @method static emergency($message, array $context = [])
 * @method static crit($message, array $context = [])
 * @method static alert($message, array $context = [])
 * @method static err($message, array $context = [])
 * @method static error($message, array $context = [])
 * @method static info($message, array $context = [])
 * @method static log($level, $message, array $context = [])
 * @method static notice($message, array $context = [])
 * @method static warn($message, array $context = [])
 * @method static warning($message, array $context = [])
 */
class Log
{
    /**
     * The logs directory.
     *
     * @var string
     */
    private static $logsDirectory = '';

    /**
     * The minimum log level.
     *
     * @var int
     */
    private static $logLevel = Logger::ERROR;

    /**
     * The instance of the Logger class.
     *
     * @var Logger
     */
    private static $instance = null;

    /**
     * Triggered when invoking inaccessible methods in a static context.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $arguments)
    {
        $logger = static::getInstance();

        if (method_exists($logger, $method) === false) {
            throw new \Exception('Method \'' . $method . '\' does not exist.');
        }

        return call_user_func_array([$logger, $method], $arguments);
    }

    /**
     * - Checks if an instance was created and creates one if not.
     * - Returns the instance of the Logger class.
     *
     * @return Logger
     */
    private static function getInstance()
    {
        if (static::$instance === null) {
            static::$logsDirectory = path('/logs');
            static::$instance      = new Logger('php-deploy');
            static::$instance->pushHandler(new RotatingFileHandler(static::$logsDirectory . '/php-deploy.log', 7, static::$logLevel));
        }

        return static::$instance;
    }
}
