<?php

namespace Elbucho\Alert\Tests;
use Elbucho\Alert\AlertTrait;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class AlertTraitTest extends TestCase
{
    use AlertTrait;

    /**
     * Last Logger message
     *
     * @access  protected
     * @var     string
     */
    protected $lastLoggerMessage = '';

    /**
     * Last Output message
     *
     * @access  protected
     * @var     string
     */
    protected $lastOutputMessage = '';

    /**
     * Data provider for testOutputAndLogging
     *
     * @access  public
     * @param   void
     * @return  array
     */
    public function outputAndLoggingProvider(): array
    {
        return array(
            [OutputInterface::VERBOSITY_DEBUG, Logger::DEBUG, true, false],
            [OutputInterface::VERBOSITY_DEBUG, Logger::INFO, true, true],
        );
    }

    /**
     * Test the output to both the OutputInterface and Logger Interface based on the
     * provided Verbosity levels
     *
     * @access  public
     * @param   int     $verbosity
     * @param   int     $level
     * @param   bool    $outputMessage
     * @param   bool    $loggerMessage
     * @dataProvider    outputAndLoggingProvider
     */
    public function testOutputAndLogging(int $verbosity, int $level, bool $outputMessage, bool $loggerMessage)
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->setMethods(['addRecord'])
            ->getMock();
        $logger->method('addRecord')->willReturnCallback([$this, 'setLoggerMessage']);

        /** @noinspection PhpParamsInspection */
        $this->setLogger($logger);

        $output = $this->getMockBuilder(OutputInterface::class)
            ->setMethods(['getVerbosity','writeln'])
            ->getMock();
        $output->method('getVerbosity')->willReturn($verbosity);
        $output->method('writeln')->willReturnCallback([$this, 'setOutputMessage']);

        /** @noinspection PhpParamsInspection */
        $this->setOutput($output);

        $message = md5(rand());

        try {
            $this->alert($level, $message);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        if ($outputMessage) {
            $this->assertEquals($message, $this->lastOutputMessage);
        } else {
            $this->assertNotEquals($message, $this->lastOutputMessage);
        }

        if ($loggerMessage) {
            $this->assertEquals($message, $this->lastLoggerMessage);
        } else {
            $this->assertNotEquals($message, $this->lastLoggerMessage);
        }
    }

    /**
     * Update the internal $lastLoggerMessage variable with the string that was sent to
     * the Logger class via an addRecord call
     *
     * @access  protected
     * @param   int     $level
     * @param   string  $message
     * @return  void
     */
    protected function setLoggerMessage(int $level, string $message)
    {
        $this->lastLoggerMessage = $message;
    }

    /**
     * Update the internal $lastOutputMessage variable with the string that was sent to
     * the OutputInterface class via a writeln call
     *
     * @access  protected
     * @param   string  $message
     * @return  void
     */
    protected function setOutputMessage(string $message)
    {
        $this->lastOutputMessage = $message;
    }
}