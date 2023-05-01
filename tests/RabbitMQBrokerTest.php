<?php

use ByJG\MessagingClient\Broker\Queue;
use ByJG\MessagingClient\Broker\RabbitMQBroker;
use ByJG\MessagingClient\Exception\StopBrokerException;
use ByJG\MessagingClient\Message\Message;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class RabbitMQBrokerTest extends TestCase
{
    public function testPublish()
    {
        $broker = new RabbitMQBroker();
        $broker->setUp(new Uri("amqp://guest:guest@localhost:5672/"));

        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $broker->publish($message);

        $this->assertTrue(true);
    }

    public function testConsume()
    {
        $broker = new RabbitMQBroker();
        $broker->setUp(new Uri("amqp://guest:guest@localhost:5672/"));

        $queue = new Queue("test");
        $broker->consume($queue, function (Message $message) {
            $this->assertEquals("body", $message->getBody());
            $this->assertEquals("test", $message->getQueue()->getName());
            $this->assertNull($message->getQueue()->getTopic());
            $this->assertEquals([], $message->getQueue()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Message $message) {
            return Message::NACK | Message::EXIT;
        });
    }
}