<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

use Illuminate\Support\Collection;
use PhpDeploy\Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ReleaseRollbackCommand class.
 */
class ReleaseRollbackCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private $repository;

    /**
     * @var bool
     */
    private $isDryRun = false;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $releasesDirectory;

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('release:rollback')
            ->setDescription('Perform a rollback of the last release by pointing \'current\' to the second newest release and removing the newest release')
            ->addArgument('repository', InputArgument::REQUIRED, 'The full name of the repository')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'A dry run only shows an ouput of what is changed.');
    }

    /**
     * Executes the command.
     *
     * @throws \Exception
     * @return integer
     */
    protected function executeCommand()
    {
        $this->repository = $this->input->getArgument('repository');
        $this->isDryRun   = $this->input->getOption('dry-run');
        $configuration    = array_get(\PhpDeploy\config($this->repository), 'deployment', []);

        if (empty($configuration)) {
            throw new \Exception(sprintf('No configuration found for \'%s\'', $this->repository));
        }

        $this->releasesDirectory = array_get($configuration, 'to');
        $this->fileSystem        = new Filesystem();

        try {
            $this->changeSymlink();
            $this->deleteRelease();

            return 0;
        } catch (\Exception $e) {
            Log::error('Rollback failed: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Changing symlink to the second newest release.
     */
    private function changeSymlink()
    {
        $release = $this->getReleases()->last();
        $renamed = str_replace('_', '', $release);

        $this->writeln('Changing symlink to ' . $renamed);

        if ($this->isDryRun === false) {
            $this->fileSystem->rename($release, $renamed);
            $this->fileSystem->symlink($renamed, $this->releasesDirectory . '/current');
        }
    }

    /**
     * Deleting the newest release.
     */
    private function deleteRelease()
    {
        $release = $this->getReleases()->first();

        $this->writeln('Deleting ' . $release);

        if ($this->isDryRun === false) {
            $this->fileSystem->remove($release);
        }
    }

    /**
     * Returns the 2 last directories of the releases.
     *
     * @return  Collection
     * @throws \Exception
     */
    private function getReleases()
    {
        $glob     = glob($this->releasesDirectory . '/releases/*', GLOB_ONLYDIR);
        $releases = collect($glob)
            ->sortBy(function ($release) {
                return preg_replace('/.*\/_?([0-9]+)$/', '$1', $release);
            })
            ->reverse()
            ->slice(0, 2);

        if ($releases->count() === 1) {
            throw new \Exception('Only one release left. Unable to perform rollback.');
        }

        return $releases;
    }
}
