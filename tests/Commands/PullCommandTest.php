<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Tests\Commands;

use PhpDeploy\Commands\AbstractCommand;
use PhpDeploy\Commands\PullCommand;
use PhpDeploy\Tests\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * PullCommandTest class.
 */
class PullCommandTest extends TestCase
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
     * @var string
     */
    private $directory;

    /**
     * - Adds the command.
     * - Searches for the command by its name.
     * - Creates a new CommandTester instance with the command as argument.
     */
    protected function setUp()
    {
        $this->application->add(new PullCommand());

        $this->command       = $this->application->find('pull');
        $this->commandTester = new CommandTester($this->command);

        $this->createDirectory();
        $this->cloneRepository();
    }

    /**
     * Creates the directory to clone the repository to.
     */
    private function createDirectory()
    {
        $this->directory = $this->createTemporaryDirectory('repo');

        $this->updateConfigFile('rolfdenhartog/wordpress-theme.branches.master', $this->directory);
    }

    /**
     * Clones this repository and checks out an old commit to be able to do a 'git pull'.
     */
    private function cloneRepository()
    {
        exec('git clone https://github.com/rolfdenhartog/wordpress-theme.git -b master ' . $this->directory . ' > /dev/null 2>&1');
        exec('cd ' . $this->directory . ' && git checkout d08b38c' . ' > /dev/null 2>&1');
    }

    /**
     * Tests the main command.
     */
    public function testBasicCommand()
    {
        $this->commandTester->execute([
            'command'    => $this->command->getName(),
            'repository' => 'rolfdenhartog/wordpress-theme',
            'branch'     => 'master',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('Code pulled!', $output);
    }

    /**
     * Tests the post pull hook.
     */
    public function testPostPullHook()
    {
        // create filename
        $file = $this->directory . '/.git/hooks/post-pull';
        // create file
        file_put_contents($file, 'echo "post pull hook"');
        // change mode
        chmod($file, 0755);

        $this->commandTester->execute([
            'command'    => $this->command->getName(),
            'repository' => 'rolfdenhartog/wordpress-theme',
            'branch'     => 'master',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('post pull hook', $output);
    }

    /**
     * Tests the post pull hook with wrong permissions.
     */
    public function testNonExecutablePostPullHook()
    {
        // create filename
        $file = $this->directory . '/.git/hooks/post-pull';
        // create file
        file_put_contents($file, 'echo "post pull hook"');
        // change mode
        chmod($file, 0644);

        $this->expectException(\Exception::class);

        $this->commandTester->execute([
            'command'    => $this->command->getName(),
            'repository' => 'rolfdenhartog/wordpress-theme',
            'branch'     => 'master',
        ]);
    }

    /**
     * Tests the command without branches set.
     */
    public function testNoBranches()
    {
        $file                 = \PhpDeploy\path('/logs/tests/php-deploy-config.php');
        $fileContents         = file_get_contents($file);
        $fileContentsReplaced = preg_replace('/(\'branches\' +=> \[).*(\],)/msU', '$1$2', $fileContents);

        file_put_contents($file, $fileContentsReplaced);

        $this->expectException(\Exception::class);

        $this->commandTester->execute([
            'command'    => $this->command->getName(),
            'repository' => 'rolfdenhartog/wordpress-theme',
            'branch'     => 'master',
        ]);
    }
}
