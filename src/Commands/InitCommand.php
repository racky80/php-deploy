<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

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
     * Loop files property and call the copyFile method.
     *
     * @return int
     * @throws \Exception
     */
    protected function executeCommand()
    {
        foreach ($this->files as $file) {
            $this->copyFile($file);
        }
    }

    /**
     * - Checks if the file is already copied. Returns 0 if the file already exists.
     * - Copies the file and returns 0 on success.
     * - Throws an exception if copying failed.
     *
     * @param string $file
     * @return bool
     * @throws \Exception
     */
    private function copyFile(string $file)
    {
        $from = \PhpDeploy\path('/files/' . $file);
        $to   = \PhpDeploy\path('/../../..') . '/' . $file;

        if (file_exists($to)) {
            $this->writeln(sprintf('File \'%s\' already copied', $file));

            return true;
        }

        $this->writeln(sprintf('Copying file \'%s\'', $file));

        if (copy($from, $to)) {
            $this->writeln(sprintf('File \'%s\' copied', $file));

            return true;
        }

        throw new \Exception(sprintf('Copying file \'%s\' failed', $file));
    }
}
