<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

/**
 * Include composer auto loader.
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Remove the files from the logs directory.
 */
shell_exec('rm -rf ' . \PhpDeploy\path('/logs/tests') . '/*');

/**
 * Changes the working directory.
 */
chdir(\PhpDeploy\path('/logs/tests'));
