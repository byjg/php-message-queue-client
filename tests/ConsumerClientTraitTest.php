<?php

namespace Tests;

use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\ConsumerClient;
use Tests\Fixtures\ConsumerClientError;

class ConsumerClientTraitTest extends TestCase
{
    public function testConsumerClient()
    {
        // The message to be sent
        $message = new Message("body");

        // The ConsumerClient class is the class to be tested
        $client = new ConsumerClient(
            $this, [
                "Processing: " . $message->getBody(),
                "Success: " . $message->getBody(),
            ],
            $message
        );

        // Preparing the envelope and sending the message
        $envelope = new Envelope($client->getPipe(), $message);
        $client->getConnector()->publish($envelope);

        // Consuming the message - It will trigger the tests
        $client->consume();

        // The tests are inside the LoggerAssert class and ConsumerClient class
        $this->assertTrue(true);
    }

    public function testConsumerClientInvalid()
    {
        // The message to be sent
        $message = new Message("body");

        // The ConsumerClient class is the class to be tested
        $client = new ConsumerClientError(
            $this,
            [
                "Processing: " . $message->getBody(),
                "Error: " . $message->getBody(),
            ],
            $message
        );

        // Preparing the envelope and sending the message
        $envelope = new Envelope($client->getPipe(), $message);
        $client->getConnector()->publish($envelope);

        // Consuming the message - It will trigger the tests
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Process error");
        $client->consume();
    }

}
