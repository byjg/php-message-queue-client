<?php

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

class LoggerAssert implements \Psr\Log\LoggerInterface
{

    protected TestCase $testCase;

    protected array $expectedLogs = [];

    public function __construct(TestCase $testCase, $expectedLogs)
    {
        $this->testCase = $testCase;
        $this->expectedLogs = $expectedLogs;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = array())
    {
        $expectedMessage = array_shift($this->expectedLogs);
        $this->testCase->assertEquals($expectedMessage, $message);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        throw new \Exception("Not implemented");
    }
}