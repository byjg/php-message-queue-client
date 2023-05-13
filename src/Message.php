<?php

namespace ByJG\MessageQueueClient;

class Message
{
    const ACK     = 0b000;
    const NACK    = 0b001;
    const REQUEUE = 0b011;
    const EXIT    = 0b100;

    protected $body;

    protected $properties = [];

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function withProperty($property, $value)
    {
        $this->properties[$property] = $value;
        return $this;
    }

    public function withProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }
}
