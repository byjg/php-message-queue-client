<?php


use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class MockConnectorTest extends TestCase
{
    public function testPublishConsume()
    {
        ConnectorFactory::registerConnector(MockConnector::class);
        $connector = ConnectorFactory::create(new Uri("mock://local"));

        $this->assertInstanceOf(MockConnector::class, $connector);

        $pipe = new Pipe("test");
        $message = new Message("body");
        $connector->publish(new Envelope($pipe, $message));

        $connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("body", $envelope->getMessage()->getBody());
            return Message::ACK;
        }, function () {
            $this->assertTrue(false);
            return Message::NACK;
        });
    }


}