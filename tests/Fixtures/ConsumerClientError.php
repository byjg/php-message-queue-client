<?php

namespace Tests\Fixtures;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\ConsumerClientInterface;
use ByJG\MessageQueueClient\ConsumerClientTrait;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConsumerClientError extends ConsumerClient
{
    public function processMessage(\ByJG\MessageQueueClient\Message $message): void
    {
        throw new InvalidArgumentException("Process error");
    }
}
