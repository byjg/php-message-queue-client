<?php

namespace ByJG\MessageQueueClient\Connector;

class Pipe
{
    protected string $pipe;

    protected array $properties = [];

    /** @var Pipe|null */
    protected ?Pipe $deadLetter = null;

    public function __construct($pipe)
    {
        $this->pipe = $pipe;
    }

    public function getName(): string
    {
        return $this->pipe;
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

    public function setProperty(string $property, mixed $value): self
    {
        $this->properties[$property] = $value;
        return $this;
    }

    public function setPropertyIfNull(string $property, mixed $value): self
    {
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = $value;
        }
        return $this;
    }

    public function deleteProperty(string $property): self
    {
        unset($this->properties[$property]);
        return $this;
    }

    public function getProperty(string $property, mixed $default = null): mixed
    {
        return $this->properties[$property] ?? $default;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getDeadLetter(): ?Pipe
    {
        return $this->deadLetter;
    }

    public function withDeadLetter(Pipe $deadLetter): self
    {
        $this->deadLetter = $deadLetter;
        return $this;
    }
}
