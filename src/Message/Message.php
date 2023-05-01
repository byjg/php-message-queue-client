<?php

namespace ByJG\MessagingClient\Message;

use ByJG\MessagingClient\Broker\Queue;

class Message
{
    protected $body;

    /** @var Queue */
    protected $queue;

    protected $headers = [];

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
}
