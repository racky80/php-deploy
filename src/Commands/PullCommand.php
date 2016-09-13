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
     * The full name of the repository (username-or-team/repository).
     *
     * @var string
     */
    private $repository = null;

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
            ->addArgument('repository', InputArgument::REQUIRED, 'The full name (username-or-team/repository) of the repository')
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
        $this->repository = $this->input->getArgument('repository');
        $this->branch     = $this->input->getArgument('branch');

        try {
            $configuration = \PhpDeploy\config($this->repository);
            $branches      = array_get($configuration, 'branches', []);

            if (empty($branches)) {
                throw new \Exception('No branches set');
            }

            $this->workingDirectory = array_get($branches, $this->branch);
            $this->resetHead();
            $this->pullCode();

            $this->writeln('Code pulled!');

            return 0;
        } catch (\Exception $e) {
            Log::error('Pulling code failed: ' . $e->getMessage() . '. Check the log file.');

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

            throw new \Exception('Command \'git pull\' failed');
        }
    }

    /**
     * Calls the custom git hook 'post-pull'. This file must be executable! Because there is no git hook after the `git
     * pull` command, you can use this to run code after a `git pull`.
     *
     * @throws \Exception
     */
    protected function afterExecution()
    {
        $gitHook = $this->workingDirectory . '/.git/hooks/post-pull';

        if (file_exists($gitHook) === false) {
            return;
        }

        if (is_executable($gitHook) === false) {
            throw new \Exception('Git hook is not executable');
        }

        $process = new Process($gitHook);
        $process->run();
    }
}
