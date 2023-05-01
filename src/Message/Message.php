<?php

namespace ByJG\MessagingClient\Message;

use ByJG\MessagingClient\Broker\Queue;

class Message
{
    const ACK     = 0b000;
    const NACK    = 0b001;
    const REQUEUE = 0b011;
    const EXIT    = 0b100;

    protected $body;

    /** @var Queue */
    protected $queue;

    protected $headers = [];

    /** @var Queue */
    protected $deadLetterQueue = null;

    public function __construct($body, Queue $queue)
    {
        $this->body = $body;
        $this->queue = $queue;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function withHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function withHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getDeadLetterQueue()
    {
        return $this->deadLetterQueue;
    }

    public function withDeadLetterQueue(Queue $deadLetterQueue)
    {
        $this->deadLetterQueue = $deadLetterQueue;
        return $this;
    }
}
