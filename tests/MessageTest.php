<?php

namespace Tests;

use ByJG\MessageQueueClient\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testGetBody()
    {
        $message = new Message("body");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals([], $message->getProperties());
    }

    public function testGetBodyWithProperties()
    {
        $message = new Message("body");
        $message->withProperties(["key" => "value"]);
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals(["key" => "value"], $message->getProperties());
    }

    public function testGetBodyWithProperty()
    {
        $message = new Message("body");
        $message->withProperty("key", "value");
        $this->assertEquals("body", $message->getBody());
        $this->assertEquals(["key" => "value"], $message->getProperties());
    }
}
