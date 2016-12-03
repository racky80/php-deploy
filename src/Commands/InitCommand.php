<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

use Symfony\Component\Filesystem\Exception\IOException;

/**
 * InitCommand class.
 */
class InitCommand extends AbstractCommand
{
    /**
     * Files to be copied.
     *
     * @var array
     */
    private $files = [
        'php-deploy-config.php',
        'webhook-bitbucket.php',
        'webhook-github.php',
    ];

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a configuration file for deploy in your project');
    }

    /**
     * Checks the directory if it's writeable and loops the files and calls the copyFile method.
     *
     * @throws IOException
     */
    protected function executeCommand()
    {
        if (is_writeable(getcwd()) === false) {
            throw new IOException(sprintf('Directory not writable \'%s\'', getcwd()));
        }

        collect($this->files)->each(function ($file) {
            $this->copyFile($file);
        });
    }

    /**
     * Checks if the file is already exists and writes a message to the output or copies the file and outputs the
     * message.
     *
     * @param string $file
     * @return bool
     * @throws \Exception
     */
    private function copyFile($file)
    {
        $from = \PhpDeploy\path('/files/' . $file);
        $to   = getcwd() . '/' . $file;

        if (file_exists($to)) {
            $this->writeln(sprintf('File \'%s\' already copied', $file));
        } else {
            copy($from, $to);

            $this->writeln(sprintf('File \'%s\' copied', $file));
        }
    }
}
