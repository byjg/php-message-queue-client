<?php

namespace ByJG\MessagingClient;

class Message
{
    const ACK     = 0b000;
    const NACK    = 0b001;
    const REQUEUE = 0b011;
    const EXIT    = 0b100;

    protected $body;

    protected $headers = [];

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
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
