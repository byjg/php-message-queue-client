<?php

use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use PHPUnit\Framework\TestCase;

class EnvelopeTest extends TestCase
{
    public function testGetters()
    {
        $pipe = new Pipe("foo");
        $message = new Message("bar");

        $envelope = new Envelope($pipe, $message);

        $this->assertEquals($pipe, $envelope->getPipe());
        $this->assertEquals($message, $envelope->getMessage());
    }
}
