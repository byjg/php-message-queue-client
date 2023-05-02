<?php

use ByJG\MessagingClient\Broker\Queue;
use ByJG\MessagingClient\Broker\RabbitMQBroker;
use ByJG\MessagingClient\Exception\StopBrokerException;
use ByJG\MessagingClient\Message\Message;
use ByJG\Util\Uri;
use ParagonIE\ConstantTime\Base64DotSlash;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;

class RabbitMQBrokerTest extends TestCase
{
    protected $broker;

    public function setUp(): void
    {
        $this->broker = new RabbitMQBroker();
        $this->broker->setUp(new Uri("amqp://guest:guest@localhost:5672/"));
    }

    public function testClearQueues()
    {
        // We are not using tearDown() because we want to keep the queues for the other tests

        $connection = $this->broker->getConnection();
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

        $queue = new Queue("test");
        $message = new Message("body", $queue);
        $this->broker->publish($message);

        $this->broker->consume($queue, function (Message $message) {
            $this->assertEquals("body", $message->getBody());
            $this->assertEquals("test", $message->getQueue()->getName());
            $this->assertEquals("test", $message->getQueue()->getTopic());
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
            ], $message->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
            ], $message->getQueue()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });
    }

    public function testPublishConsumeRequeue()
    {
        $queue = new Queue("test");
        $message = new Message("body_requeue", $queue);
        $this->broker->publish($message);

        $this->broker->consume($queue, function (Message $message) {
            $this->assertEquals("body_requeue", $message->getBody());
            $this->assertEquals("test", $message->getQueue()->getName());
            $this->assertEquals("test", $message->getQueue()->getTopic());
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
            ], $message->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
            ], $message->getQueue()->getProperties());
            return Message::REQUEUE | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });
    }

    public function testConsumeMessageRequeued()
    {
        $queue = new Queue("test");

        $this->broker->consume($queue, function (Message $message) {
            $this->assertEquals("body_requeue", $message->getBody());
            $this->assertEquals("test", $message->getQueue()->getName());
            $this->assertEquals("test", $message->getQueue()->getTopic());
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
            ], $message->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
            ], $message->getQueue()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });
    }

    public function testPublishConsumeWithDlq()
    {
        $queue = new Queue("test2");
        $dlqQueue = new Queue("dlq_test2");
        $queue->withDeadLetterQueue($dlqQueue);

        // Post and consume a message
        $message = new Message("bodydlq", $queue);
        $this->broker->publish($message);

        $this->broker->consume($queue, function (Message $message) {
            $this->assertEquals("bodydlq", $message->getBody());
            $this->assertEquals("test2", $message->getQueue()->getName());
            $this->assertEquals("test2", $message->getQueue()->getTopic());
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
            ], $message->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
            ], $message->getQueue()->getProperties());
            return Message::ACK | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });

        // Post and reject  a message (NACK, to send to the DLQ)
        $message = new Message("bodydlq_2", $queue);
        $this->broker->publish($message);

        $this->broker->consume($queue, function (Message $message) {
            $this->assertEquals("bodydlq_2", $message->getBody());
            $this->assertEquals("test2", $message->getQueue()->getName());
            $this->assertEquals("test2", $message->getQueue()->getTopic());
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
            ], $message->getHeaders());
            $this->assertEquals([
                "exchange_type" => "direct",
            ], $message->getQueue()->getProperties());
            return Message::NACK | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });

        // Consume the DLQ
        $this->broker->consume($dlqQueue, function (Message $message) {
            $this->assertEquals("bodydlq_2", $message->getBody());
            $this->assertEquals("dlq_test2", $message->getQueue()->getName());
            $this->assertEquals("dlq_test2", $message->getQueue()->getTopic());
            $headers = $message->getHeaders();
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
            ], $message->getQueue()->getProperties());
            return Message::NACK | Message::EXIT;
        }, function (Message $message, $ex) {
            throw $ex;
        });

    }

}
