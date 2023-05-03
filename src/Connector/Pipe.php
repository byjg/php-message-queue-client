<?php

namespace ByJG\MessageQueueClient\Connector;

class Pipe
{
    protected $pipe;

    protected $properties = [];

    /** @var Pipe */
    protected $deadLetter = null;

    public function __construct($pipe)
    {
        $this->pipe = $pipe;
    }

    public function getName()
    {
        return $this->pipe;
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

    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;
        return $this;
    }

    public function setPropertyIfNull($property, $value)
    {
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = $value;
        }
        return $this;
    }

    public function deleteProperty($property)
    {
        unset($this->properties[$property]);
        return $this;
    }

    public function getProperty($property, $default = null)
    {
        return $this->properties[$property] ?? $default;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getDeadLetter()
    {
        return $this->deadLetter;
    }

    public function withDeadLetter(Pipe $deadLetter)
    {
        $this->deadLetter = $deadLetter;
        return $this;
    }
}
