<?php

use ByJG\MessagingClient\Broker\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testGetQueueName()
    {
        $queue = new Queue("test");
        $this->assertEquals("test", $queue->getName());
        $this->assertNull($queue->getTopic());
        $this->assertEquals([], $queue->getProperties());
    }

    public function testGetQueueNameWithTopic()
    {
        $queue = new Queue("test", "topic");
        $this->assertEquals("test", $queue->getName());
        $this->assertEquals("topic", $queue->getTopic());
        $this->assertEquals([], $queue->getProperties());
    }

    public function testGetQueueNameWithTopicAndProperties()
    {
        $queue = new Queue("test", "topic");
        $queue->withProperties(["key" => "value"]);
        $this->assertEquals("test", $queue->getName());
        $this->assertEquals("topic", $queue->getTopic());
        $this->assertEquals(["key" => "value"], $queue->getProperties());
    }
}