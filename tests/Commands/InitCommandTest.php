<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Tests\Commands;

use PhpDeploy\Commands\AbstractCommand;
use PhpDeploy\Commands\InitCommand;
use PhpDeploy\Tests\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * InitCommandTest class.
 */
class InitCommandTest extends TestCase
{
    /**
     * @var AbstractCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * - Adds the init command.
     * - Searches for the command by its name.
     * - Creates a new CommandTester instance with the command as argument.
     */
    protected function setUp()
    {
        $this->application->add(new InitCommand());

        $this->command       = $this->application->find('init');
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * The first call copies the files. The files should not exist.
     */
    public function testCopyingFiles()
    {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('File \'webhook-github.php\' copied', $output);
    }

    /**
     * The second call should return a different result: the files already exists.
     */
    public function testFilesAlreadyCopied()
    {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('File \'webhook-github.php\' already copied', $output);
    }

    /**
     * This test should check if the exception is thrown if the directory isn't writeable.
     */
    public function testUnwriteableDirectory()
    {
        $tmpDirectory = sys_get_temp_dir();
        $prefix       = 'unwriteable';

        // remove old directories
        shell_exec('rm -rf ' . $tmpDirectory . '/' . $prefix . '*');
        // create path
        $unwriteable = tempnam($tmpDirectory, $prefix . '-');

        unlink($unwriteable);
        mkdir($unwriteable);
        chdir($unwriteable);
        chmod($unwriteable, 0444);

        $this->expectException(IOException::class);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
    }
}
