<?php

namespace Elbucho\Alert;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

trait AlertTrait
{
    /**
     * Output Interface
     *
     * @access  protected
     * @var     OutputInterface
     */
    protected $output;

    /**
     * Logging Interface
     *
     * @access  protected
     * @var     Logger
     */
    protected $logger;

    /**
     * Output Setter
     *
     * @access  public
     * @param   OutputInterface $output
     * @return  void
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Logger Setter
     *
     * @access  public
     * @param   Logger  $logger
     * @return  void
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Send alerts
     *
     * @access  protected
     * @param   int     $level
     * @param   string  $message
     * @return  void
     * @throws  \Exception
     */
    protected function alert($level, $message)
    {
        if ( ! isset($this->output) or ! $this->output instanceof OutputInterface) {
            throw new \Exception('Invalid output interface specified');
        }

        $verbosity = $this->output->getVerbosity();

        if ($verbosity == OutputInterface::VERBOSITY_DEBUG) {
            if ($level == Logger::DEBUG) {
                if ($this->output instanceof ConsoleOutput) {
                    $error = $this->output->getErrorOutput();
                    $error->writeln($message);
                }
            } else {
                $this->output->writeln($message);
            }
        } elseif ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE and $level >= Logger::INFO) {
            $this->output->writeln($message);
        } elseif ($verbosity >= OutputInterface::VERBOSITY_VERBOSE and $level >= Logger::NOTICE) {
            $this->output->writeln($message);
        } elseif ($verbosity >= OutputInterface::VERBOSITY_NORMAL and $level >= Logger::WARNING) {
            $this->output->writeln($message);
        } elseif ($level >= Logger::ERROR) {
            if ($this->output instanceof ConsoleOutput) {
                $error = $this->output->getErrorOutput();
                $error->writeln($message);
            }
        }

        if (isset($this->logger) and $this->logger instanceof Logger) {
            $this->logger->addRecord($level, $message);
        }

        if ($level >= Logger::CRITICAL) {
            throw new \Exception($message);
        }
    }
}