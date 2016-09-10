<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

use PhpDeploy\Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * PullCommand class.
 */
class PullCommand extends AbstractCommand
{
    /**
     * The name of the application.
     *
     * @var string
     */
    private $application = null;

    /**
     * The name of the branch.
     *
     * @var string
     */
    private $branch = null;

    /**
     * The directory of the repository.
     *
     * @var string
     */
    private $workingDirectory = null;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('pull')
            ->setDescription('Resets the head of a Git repository and pulls the code')
            ->addArgument('application', InputArgument::REQUIRED, 'The name of the application')
            ->addArgument('branch', InputArgument::REQUIRED, 'The name of the branch to pull');
    }

    /**
     * Executes the command.
     *
     * @return int
     * @throws \Exception
     */
    protected function executeCommand()
    {
        $this->application      = $this->input->getArgument('application');
        $this->branch           = $this->input->getArgument('branch');

        $configuration = \PhpDeploy\config($this->application);
        $branches      = array_get($configuration, 'branches', []);

        if (empty($branches)) {
            throw new \Exception('No branches set');
        }

        $this->workingDirectory = array_get($branches, $this->branch);

        try {
            $this->resetHead();
            $this->pullCode();

            $this->writeln('Deployed!');

            return 0;
        } catch (\Exception $e) {
            Log::error('Deploying failed: ' . $e->getMessage() . '. Check the log file.');

            throw $e;
        }
    }

    /**
     * Resets the head.
     *
     * @throws \Exception
     */
    private function resetHead()
    {
        $this->writeln('Resetting HEAD');

        $process = new Process('cd ' . $this->workingDirectory . ' && git reset --hard HEAD');
        $process->run();

        if ($process->isSuccessful() === false) {
            Log::error($process->getOutput());

            throw new \Exception('Resetting head failed');
        }
    }

    /**
     * Pulls the code from Bitbucket.
     *
     * @throws \Exception
     */
    private function pullCode()
    {
        $this->writeln('Pulling code');

        $process = new Process('cd ' . $this->workingDirectory . ' && git pull origin ' . $this->branch);
        $process->run();

        if ($process->isSuccessful() === false) {
            Log::error($process->getOutput());

            throw new \Exception('Pulling code failed');
        }
    }
}
