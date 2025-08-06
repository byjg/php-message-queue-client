<?php

namespace ByJG\MessageQueueClient;

class Message
{
    const ACK     = 0b0001;
    const NACK    = 0b0010;
    const REQUEUE = 0b0100;
    const EXIT    = 0b1000;
    protected mixed $body;

    protected array $properties = [];

    public function __construct(mixed $body)
    {
        $this->body = $body;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function withProperty(string $property, mixed $value): self
    {
        $this->properties[$property] = $value;
        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }
}
