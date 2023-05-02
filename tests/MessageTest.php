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
    }

    public function testGetBodyWithHeaders()
    {
        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $message->withHeaders(["key" => "value"]);
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals($queue, $message->getQueue());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
    }

    public function testGetBodyWithHeader()
    {
        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $message->withHeader("key", "value");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals($queue, $message->getQueue());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
    }
}
