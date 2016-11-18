<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ReleaseCreateCommand class.
 */
class ReleaseCreateCommand extends AbstractCommand
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
     * @var int
     */
    private $releasesToKeep = 5;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var array
     */
    private $ignore = [];

    /**
     * @var array
     */
    private $symlinks = [];

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var string
     */
    private $releaseDirectory;

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('release:create')
            ->setDescription('Deploys a website or application by making a new release directory and copying the files')
            ->addArgument('repository', InputArgument::REQUIRED, 'The repository of the website or application you want to to be released')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Test the deployment by only showing the output');
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
            throw new \Exception(sprintf('No configuration found to deploy \'%s\'', $this->repository));
        }

        $this->fileSystem = new Filesystem();
        $this->from       = array_get($configuration, 'from');
        $this->to         = array_get($configuration, 'to');
        $this->ignore     = array_merge(['/.git'], array_get($configuration, 'ignore'));
        $this->symlinks   = array_get($configuration, 'symlinks');
        $this->scripts    = array_get($configuration, 'scripts', []);

        $this->runScript('pre');
        $this->createDirectory();
        $this->copyFiles();
        $this->createSymlinks();
        $this->changeCurrent();
        $this->renameOldReleases();
        $this->deleteOldReleases();
        $this->runScript('post');

        exit(0);
    }

    /**
     * Executes commands.
     *
     * @param string $type
     */
    private function runScript($type)
    {
        $commands = (array) array_get($this->scripts, $type, []);

        foreach ($commands as $command) {
            shell_exec($command);
        }
    }

    /**
     * Creates the directory.
     */
    private function createDirectory()
    {
        $this->releaseDirectory = $this->to . '/releases/' . date('YmdHis');

        $this->writeln('Create directory for new release: ' . $this->releaseDirectory);

        if ($this->isDryRun === false && mkdir($this->releaseDirectory, 0755, true) === false) {
            throw new \Exception('Release directory not created');
        }
    }

    /**
     * - Gets all the directories and files from the source directory.
     * - Maps the SplFileInfo object to a simple string and removes the part which is the same as the source directory.
     * - Filters out the directories and files that needs to be ignored.
     * - Loops the files and calls the copyFile method.
     */
    private function copyFiles()
    {
        $this->writeln('<comment>Creating file list. This could take a while...</comment>');

        $recursiveDirectoryIterator = new \RecursiveDirectoryIterator($this->from, \FilesystemIterator::SKIP_DOTS);
        $recursiveIteratorIterator  = new \RecursiveIteratorIterator($recursiveDirectoryIterator, \RecursiveIteratorIterator::SELF_FIRST);

        $files = collect($recursiveIteratorIterator)
            ->map(function (\SplFileInfo $splFileInfo) {
                return str_replace($this->from, '', $splFileInfo->getPathname());
            })
            ->filter(function ($path) {
                foreach ($this->ignore as $pattern) {
                    // path and pattern starting with a slash
                    if (substr($pattern, 0, 1) === '/' && strpos($path, $pattern) === 0) {
                        $this->writeln(sprintf('Ignoring file \'%s\'', $path));

                        return false;
                    }

                    // path and pattern NOT starting with a slash
                    if (substr($pattern, 0, 1) !== '/' && strpos($path, $pattern) !== false) {
                        $this->writeln(sprintf('Ignoring file \'%s\'', $path));

                        return false;
                    }
                }

                return true;
            });

        foreach ($files as $file) {
            $this->copyFile($file);
        }
    }

    /**
     * Creates the directory or copies the file.
     *
     * @param string $path
     */
    private function copyFile($path)
    {
        $this->writeln(sprintf('Copying file \'%s\'', $path));

        if ($this->isDryRun) {
            return;
        }

        $from = $this->from . $path;
        $to   = $this->releaseDirectory . $path;

        if (is_dir($from) && mkdir($to) === false) {
            $this->writeln(sprintf('<error>Directory \'%s\' not created</error>', $path));
        } elseif (is_file($from) && copy($from, $to) === false) {
            $this->writeln(sprintf('<error>File \'%s\' not copied</error>', $path));
        }
    }

    /**
     * Creates the symlinks.
     */
    private function createSymlinks()
    {
        foreach ($this->symlinks as $original => $symlink) {
            $this->writeln('Creating symlink: ' . $original . ' => ' . $symlink);

            $from = $this->to . $original;
            $to   = $this->releaseDirectory . $symlink;

            if ($this->isDryRun === false && symlink($from, $to) === false) {
                $this->writeln(sprintf('<error>Symlink not created (%s => %s)</error>', $original, $symlink));
            }
        }
    }

    /**
     * Changes the symlink for 'current'.
     */
    private function changeCurrent()
    {
        if ($this->isDryRun) {
            return;
        }

        $from = $this->releaseDirectory;
        $to   = $this->to . '/current';

        unlink($to);

        if (symlink($from, $to) === false) {
            $this->writeln(sprintf('<error>Symlink not created (%s => %s)</error>', $from, $to));
        }
    }

    /**
     * Renames the old releases by adding an underscore to the name.
     */
    private function renameOldReleases()
    {
        collect(glob($this->to . '/releases/*', GLOB_ONLYDIR))
            ->reject(function ($directory) {
                return $directory === $this->releaseDirectory || substr(pathinfo($directory, PATHINFO_BASENAME), 0, 1) === '_';
            })
            ->each(function ($directory) {
                rename($directory, pathinfo($directory, PATHINFO_DIRNAME) . '/_' . pathinfo($directory, PATHINFO_BASENAME));
            });
    }

    /**
     * Deletes the old releases.
     */
    private function deleteOldReleases()
    {
        $releases = glob($this->to . '/releases/_*', GLOB_ONLYDIR);
        $count    = count($releases);

        if ($count <= $this->releasesToKeep) {
            $this->writeln('<comment>No releases found to delete</comment>');
        }

        while (count($releases) > $this->releasesToKeep) {
            $release = array_shift($releases);

            $this->writeln(sprintf('Deleting release \'%s\'', preg_replace('/.*\/([0-9]+)/', '$1', $release)));

            if ($this->isDryRun === false) {
                $this->fileSystem->remove($release);
            }
        }
    }
}
