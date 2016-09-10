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
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a configuration file for deploy in your project');
    }

    /**
     * - Checks if the configuration file is already copied. Returns 0 if the file already exists.
     * - Copies the file and returns 0 on success.
     * - Throws an exception if copying failed.
     *
     * @return int
     * @throws \Exception
     */
    protected function executeCommand()
    {
        $from = PHP_DEPLOY_DIR . '/php-deploy-config.php';
        $to   = PHP_DEPLOY_PROJECT_DIR . '/php-deploy-config.php';

        if (file_exists($to)) {
            $this->writeln('Configuration file already copied');

            return 0;
        }

        $this->writeln('Copying configuration file');

        if (copy($from, $to)) {
            $this->writeln('Configuration file copied');

            return 0;
        }

        throw new \Exception('Copying configuration file failed');
    }
}
