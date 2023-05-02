<?php

use ByJG\MessageQueueClient\Connector\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testGetQueueName()
    {
        $queue = new Queue("test");
        $this->assertEquals("test", $queue->getName());
        $this->assertNull($queue->getTopic());
        $this->assertEquals([], $queue->getProperties());
        $this->assertNull($queue->getDeadLetterQueue());
    }

    public function testGetQueueNameWithTopic()
    {
        $queue = new Queue("test", "topic");
        $this->assertEquals("test", $queue->getName());
        $this->assertEquals("topic", $queue->getTopic());
        $this->assertEquals([], $queue->getProperties());
        $this->assertNull($queue->getDeadLetterQueue());
    }

    public function testGetQueueNameWithTopicAndProperties()
    {
        $queue = new Queue("test", "topic");
        $queue->withProperties(["key" => "value"]);
        $this->assertEquals("test", $queue->getName());
        $this->assertEquals("topic", $queue->getTopic());
        $this->assertEquals(["key" => "value"], $queue->getProperties());
        $this->assertNull($queue->getDeadLetterQueue());
    }

    public function testGetDeadLetterQueueWithQueue()
    {
        $queue = new Queue("test");
        $dlq = new Queue("dlq");
        $queue->withDeadLetterQueue($dlq);
        $this->assertEquals($dlq, $queue->getDeadLetterQueue());
    }
}