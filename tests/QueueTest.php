<?php

use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Connector\RabbitMQConnector;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testGetQueueName()
    {
        $pipe = new Pipe("test");
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals([], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetterQueue());
    }

    public function testGetQueueNameWithTopic()
    {
        $pipe = new Pipe("test", "topic");
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals([], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetterQueue());
    }

    public function testGetQueueNameWithTopicAndProperties()
    {
        $pipe = new Pipe("test", "topic");
        $pipe->withProperties(["key" => "value"]);
        $this->assertEquals("test", $pipe->getName());
        $this->assertEquals(["key" => "value"], $pipe->getProperties());
        $this->assertNull($pipe->getDeadLetterQueue());
    }

    public function testGetDeadLetterQueueWithQueue()
    {
        $pipe = new Pipe("test");
        $dlq = new Pipe("dlq");
        $pipe->withDeadLetterQueue($dlq);
        $this->assertEquals($dlq, $pipe->getDeadLetterQueue());
    }
}