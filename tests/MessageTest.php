<?php

use ByJG\MessagingClient\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testGetBody()
    {
        $message = new Message("body");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals([], $message->getHeaders());
    }

    public function testGetBodyWithHeaders()
    {
        $message = new Message("body");
        $message->withHeaders(["key" => "value"]);
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
    }

    public function testGetBodyWithHeader()
    {
        $message = new Message("body");
        $message->withHeader("key", "value");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals(["key" => "value"], $message->getHeaders());
    }
}
