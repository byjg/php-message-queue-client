<?php

use ByJG\MessagingClient\Broker\Queue;
use ByJG\MessagingClient\Message\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testGetBody()
    {
        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals($queue, $message->getQueue());
        $this->assertEquals([], $message->getHeaders());
        $this->assertNull($message->getDeadLetterQueue());
    }

    public function testGetBodyWithHeaders()
    {
        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $message->withHeaders(["key" => "value"]);
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals($queue, $message->getQueue());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
        $this->assertNull($message->getDeadLetterQueue());
    }

    public function testGetBodyWithHeader()
    {
        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $message->withHeader("key", "value");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals($queue, $message->getQueue());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
        $this->assertNull($message->getDeadLetterQueue());
    }

    public function testGetDeadLetterQueueWithQueue()
    {
        $queue = new Queue("test");
        $dlq = new Queue("dlq");
        $message = new Message("body", $queue);
        $message->withDeadLetterQueue($dlq);
        $this->assertEquals($dlq, $message->getDeadLetterQueue());
    }
}