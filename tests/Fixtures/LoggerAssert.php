<?php

namespace Tests\Fixtures;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerAssert implements LoggerInterface
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
     * @throws Exception
     */
    public function emergency($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function alert($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function critical($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function error($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function warning($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function notice($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = array()): void
    {
        $expectedMessage = array_shift($this->expectedLogs);
        $this->testCase->assertEquals($expectedMessage, $message);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function debug($message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function log($level, $message, array $context = array()): void
    {
        throw new Exception("Not implemented");
    }
}