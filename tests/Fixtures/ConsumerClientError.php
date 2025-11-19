<?php

namespace Tests\Fixtures;

use ByJG\MessageQueueClient\Message;
use InvalidArgumentException;

class ConsumerClientError extends ConsumerClient
{
    #[\Override]
    public function processMessage(Message $message): void
    {
        throw new InvalidArgumentException("Process error");
    }
}
