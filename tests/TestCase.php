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
        shell_exec('rm -rf ' . $tmpDirectory . '/' . $prefix . '*');
        // create path
        $directory = tempnam($tmpDirectory, $prefix . '-');

        unlink($directory);
        mkdir($directory, 0755, true);

        return $directory;
    }
}
