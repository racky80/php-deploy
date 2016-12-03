<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;

/**
 * TestCase class.
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $pathToPhpDeploy;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->pathToPhpDeploy  = \PhpDeploy\path('/php-deploy');
        $this->workingDirectory = \PhpDeploy\path('/logs/tests');

        chdir($this->workingDirectory);

        $this->application = new Application();
    }

    /**
     * Creates a temporary directory.
     *
     * @param string $prefix
     * @return string
     */
    protected function createTemporaryDirectory(string $prefix = 'test')
    {
        $tmpDirectory = sys_get_temp_dir();

        // remove old directories
        shell_exec('rm -rf ' . $tmpDirectory . '/php-deploy-' . $prefix . '*');
        // create path
        $directory = tempnam($tmpDirectory, 'php-deploy-' . $prefix . '-');

        unlink($directory);
        mkdir($directory, 0755, true);

        return $directory;
    }

    /**
     * Replaced a string in the configuration file (generated by the InitCommandTest).
     *
     * !! This method fails if the InitCommandTest also failed.
     *
     * @param string $key
     * @param string $replace
     * @return int
     */
    protected function updateConfigFile(string $key, string $replace)
    {
        $search               = \PhpDeploy\config($key);
        $file                 = \PhpDeploy\path('/logs/tests/php-deploy-config.php');
        $fileContents         = file_get_contents($file);
        $fileContentsReplaced = str_replace($search, $replace, $fileContents);

        return file_put_contents($file, $fileContentsReplaced);
    }
}
