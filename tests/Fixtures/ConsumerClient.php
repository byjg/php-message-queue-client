<?php

namespace Tests\Fixtures;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\ConsumerClientInterface;
use ByJG\MessageQueueClient\ConsumerClientTrait;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;

class ConsumerClient implements ConsumerClientInterface
{
    use ConsumerClientTrait;

    protected TestCase $testCase;

    protected array $expectedLogs = [];

    protected Message $expectedMessage;

    protected LoggerInterface $logger;

    public function __construct(TestCase $testCase, $expectedLogs, $expectedMessage)
    {
        $this->testCase = $testCase;
        $this->expectedLogs = $expectedLogs;
        $this->expectedMessage = $expectedMessage;
        $this->logger = new LoggerAssert($this->testCase, $this->expectedLogs);
    }

    public function getPipe(): Pipe
    {
        return new Pipe('test');
    }

    public function getConnector(): ConnectorInterface
    {
        $mock = new MockConnector();
        $mock->setUp(new Uri('mock://local'));
        return $mock;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getLogOutputStart(Message $message): string
    {
        return "Processing: " . $message->getBody();
    }

    public function getLogOutputException(Throwable $exception, Message $message): string
    {
        return "Error: " . $message->getBody();
    }

    public function getLogOutputSuccess(Message $message): string
    {
        return "Success: " . $message->getBody();
    }

    public function processMessage(Message $message): void
    {
        $this->testCase->assertEquals($this->expectedMessage, $message);
    }
}
