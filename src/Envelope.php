<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\Queue;

class Envelope
{
    /** @var Message */
    protected $message;

    /** @var Queue */
    protected $queue;

    public function __construct(Queue $queue, Message $message)
    {
        $this->message = $message;
        $this->queue = $queue;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }
}
