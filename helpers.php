<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy;

/**
 * Returns the realpath of a file or directory.
 *
 * @param  string $file
 * @return string
 */
function path(string $file = '')
{
    if (substr($file, 0, 1) !== '/') {
        $file = '/' . $file;
    }

    return realpath(__DIR__ . $file);
}

/**
 * - Checks if a php-deploy.php file is created. Throws an exception if not.
 * - Includes the file which contains an array that configures the application.
 * - Checks if the array has the key. Throws an exception if not.
 * - Returns the value for a key in the array.
 *
 * @param  string $key
 * @return mixed
 * @throws \Exception
 */
function config(string $key)
{
    $path = __DIR__ . '/../../../php-deploy-config.php';

    if (file_exists($path) === false) {
        throw new \Exception('No php-deploy-config.php file found in your project. Run `php vendor/bin/php-deploy init` to start.');
    }

    $configuration = require_once $path;

    if (array_has($configuration, $key) === false) {
        throw new \Exception(sprintf('No configuration found with the key %s', $key));
    }

    return array_get($configuration, $key);
}
