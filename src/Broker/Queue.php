<?php

namespace ByJG\MessagingClient\Broker;

class Queue
{
    protected $queue;

    protected $topic;

    protected $properties = [];

    /** @var Queue */
    protected $deadLetterQueue = null;

    public function __construct($queue, $topic = null)
    {
        $this->queue = $queue;
        $this->topic = $topic;
    }

    public function getName()
    {
        return $this->queue;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function withTopic($topic)
    {
        $this->topic = $topic;
        return $this;
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

    public function getProperties()
    {
        return $this->properties;
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
