<?php

namespace Tests;

use ByJG\MessageQueueClient\Connector\Pipe;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testGetQueueName()
    {
        $pipe = new Pipe("test");
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals([], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetter());
    }

    public function testGetQueueNameWithTopic()
    {
        $pipe = new Pipe("test", "topic");
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals([], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetter());
    }

    public function testGetQueueNameWithTopicAndProperties()
    {
        $pipe = new Pipe("test", "topic");
        $pipe->withProperties(["key" => "value"]);
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals(["key" => "value"], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetter());
    }

    public function testGetDeadLetterWithQueue()
    {
        $pipe = new Pipe("test");
        $dlq = new Pipe("dlq");
        $pipe->withDeadLetter($dlq);
        $this->assertEquals($dlq, $pipe->getDeadLetter());
    }
}