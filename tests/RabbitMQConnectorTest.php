<?php

use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Connector\RabbitMQBroker;
use ByJG\MessageQueueClient\Connector\RabbitMQConnector;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class RabbitMQConnectorTest extends TestCase
{
    /** @var ConnectorInterface */
    protected $connector;

    public function setUp(): void
    {
        ConnectorFactory::registerConnector(RabbitMQConnector::class);
        $this->connector = ConnectorFactory::create("amqp://guest:guest@localhost:5672/");
    }

    public function testClearQueues()
    {
        // We are not using tearDown() because we want to keep the queues for the other tests

        $connection = $this->connector->getConnection();
        $channel = $connection->channel();
        $channel->queue_delete("test");
        $channel->exchange_delete("test");
        $channel->queue_delete("test2");
        $channel->exchange_delete("test2");
        $channel->queue_delete("dlq_test2");
        $channel->exchange_delete("dlq_test2");
        $channel->close();
        $connection->close();

        $this->assertTrue(true);
    }

    public function testPublishConsume()
    {

        $pipe = new Pipe("test");
        $message = new Message("body");
        $this->connector->publish(new Envelope($pipe, $message));

        $this->connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("body", $envelope->getMessage()->getBody());
            $this->assertEquals("test", $envelope->getPipe()->getName());
            $this->assertEquals("test", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'test',
                'delivery_tag' => 1,
                'redelivered' => false,
                'exchange' => 'test',
                'routing_key' => 'test',
                'body_size' => 4,
                'message_count' => null,
            ], $envelope->getMessage()->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
                '_x_exchange' => 'test',
                '_x_routing_key' => 'test',
            ], $envelope->getPipe()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });
    }

    public function testPublishConsumeRequeue()
    {
        $pipe = new Pipe("test");
        $message = new Message("body_requeue");
        $this->connector->publish(new Envelope($pipe, $message));

        $this->connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("body_requeue", $envelope->getMessage()->getBody());
            $this->assertEquals("test", $envelope->getPipe()->getName());
            $this->assertEquals("test", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'test',
                'delivery_tag' => 1,
                'redelivered' => false,
                'exchange' => 'test',
                'routing_key' => 'test',
                'body_size' => 12,
                'message_count' => null,
            ], $envelope->getMessage()->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
                '_x_exchange' => 'test',
                '_x_routing_key' => 'test',
            ], $envelope->getPipe()->getProperties());
            return Message::REQUEUE | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });
    }

    public function testConsumeMessageRequeued()
    {
        $pipe = new Pipe("test");

        $this->connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("body_requeue", $envelope->getMessage()->getBody());
            $this->assertEquals("test", $envelope->getPipe()->getName());
            $this->assertEquals("test", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'test',
                'delivery_tag' => 1,
                'redelivered' => true,
                'exchange' => 'test',
                'routing_key' => 'test',
                'body_size' => 12,
                'message_count' => null,
            ], $envelope->getMessage()->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
                '_x_exchange' => 'test',
                '_x_routing_key' => 'test',
                        ], $envelope->getPipe()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });
    }

    public function testPublishConsumeWithDlq()
    {
        $pipe = new Pipe("test2");
        $dlqQueue = new Pipe("dlq_test2");
        $pipe->withDeadLetterQueue($dlqQueue);

        // Post and consume a message
        $message = new Message("bodydlq");
        $this->connector->publish(new Envelope($pipe, $message));

        $this->connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("bodydlq", $envelope->getMessage()->getBody());
            $this->assertEquals("test2", $envelope->getPipe()->getName());
            $this->assertEquals("test2", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'test2',
                'delivery_tag' => 1,
                'redelivered' => false,
                'exchange' => 'test2',
                'routing_key' => 'test2',
                'body_size' => 7,
                'message_count' => null,
            ], $envelope->getMessage()->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
                '_x_exchange' => 'test2',
                '_x_routing_key' => 'test2',
            ], $envelope->getPipe()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });

        // Post and reject  a message (NACK, to send to the DLQ)
        $message = new Message("bodydlq_2");
        $this->connector->publish(new Envelope($pipe, $message));

        $this->connector->consume($pipe, function (Envelope $envelope) {
            $this->assertEquals("bodydlq_2", $envelope->getMessage()->getBody());
            $this->assertEquals("test2", $envelope->getPipe()->getName());
            $this->assertEquals("test2", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'test2',
                'delivery_tag' => 1,
                'redelivered' => false,
                'exchange' => 'test2',
                'routing_key' => 'test2',
                'body_size' => 9,
                'message_count' => null,
            ], $envelope->getMessage()->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
                '_x_exchange' => 'test2',
                '_x_routing_key' => 'test2',
            ], $envelope->getPipe()->getProperties());
            return Message::NACK | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });

        // Consume the DLQ
        $this->connector->consume($dlqQueue, function (Envelope $envelope) {
            $this->assertEquals("bodydlq_2", $envelope->getMessage()->getBody());
            $this->assertEquals("dlq_test2", $envelope->getPipe()->getName());
            $this->assertEquals("dlq_test2", $envelope->getPipe()->getProperty(RabbitMQConnector::EXCHANGE));
            $headers = $envelope->getMessage()->getHeaders();
            unset($headers['application_headers']);
            $this->assertEquals([
                'content_type' => 'text/plain',
                'delivery_mode' => 2,
                'consumer_tag' => 'dlq_test2',
                'delivery_tag' => 1,
                'redelivered' => false,
                'exchange' => 'dlq_test2',
                'routing_key' => 'test2',
                'body_size' => 9,
                'message_count' => null,
            ], $headers);
            $this->assertEquals([
                "exchange_type" => "fanout",
                '_x_exchange' => 'dlq_test2',
                '_x_routing_key' => 'dlq_test2',
            ], $envelope->getPipe()->getProperties());
            return Message::NACK | Message::EXIT;
        }, function (Envelope $envelope, $ex) {
            throw $ex;
        });

    }

}
