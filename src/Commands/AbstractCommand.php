<?php

/**
 * @author Rolf den Hartog <rolf@rolfdenhartog.nl>
 */

namespace PhpDeploy\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractCommand class.
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Executes the command.
     *
     * @return integer
     */
    abstract protected function executeCommand();

    /**
     * Sets the input and output and calls the executeCommand method which returns an integer (exit code).
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        return $this->executeCommand();
    }

    /**
     * Writes a message to the console.
     *
     * @param string $message
     */
    protected function writeln($message)
    {
        $this->output->writeln($message);
    }
}
