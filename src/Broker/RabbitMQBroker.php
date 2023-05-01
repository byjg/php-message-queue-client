<?php

namespace ByJG\MessagingClient\Broker;

use ByJG\MessagingClient\Exception\StopBrokerException;
use ByJG\MessagingClient\Message\Message;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQBroker implements BrokerInterface
{
    /** @var \ByJG\Util\Uri */
    protected $uri;

    public function setUp(\ByJG\Util\Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected function connect()
    {
        $vhost = trim($this->uri->getPath(), "/");
        if (empty($vhost)) {
            $vhost = "/";
        }

        $connection = new AMQPStreamConnection(
            $this->uri->getHost(),
            empty($this->uri->getPort()) ? 5672 : $this->uri->getPort(),
            $this->uri->getUsername(),
            $this->uri->getPassword(),
            $vhost
        );

        return $connection;
    }

    /**
     * @param AMQPStreamConnection $connection
     * @param Queue $queue
     * @return AMQPChannel
     */
    protected function createQueue($connection, Queue &$queue)
    {
        $queue->withTopic($queue->getTopic() ?? $queue->getName());
        $properties = $queue->getProperties();
        $properties['exchange_type'] = $properties['exchange_type'] ?? AMQPExchangeType::DIRECT;
        $queue->withProperties($properties);

        $channel = $connection->channel();

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue->getName(), false, true, false, false, false);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($queue->getTopic(), $properties['exchange_type'], false, true, false);

        $channel->queue_bind($queue->getName(), $queue->getTopic(), $queue->getName());

        return $channel;
    }

    protected function lazyConnect(Queue &$queue)
    {
        $connection = $this->connect();
        $channel = $this->createQueue($connection, $queue);

        return [$connection, $channel];
    }


    public function publish(Message $message)
    {
        $headers = $message->getHeaders();
        $headers['content_type'] = $headers['content_type'] ?? 'text/plain';
        $headers['delivery_mode'] = $headers['delivery_mode'] ?? AMQPMessage::DELIVERY_MODE_PERSISTENT;

        $queue = clone $message->getQueue();

        list($connection, $channel) = $this->lazyConnect($queue);

        $rabbitMQMessageBody = $message->getBody();

        $rabbitMQMessage = new AMQPMessage($rabbitMQMessageBody, $headers);

        $channel->basic_publish($rabbitMQMessage, $queue->getTopic(), $queue->getName());

        $channel->close();
        $connection->close();
    }

    public function consume(Queue $queue, \Closure $onReceive, \Closure $onError, $identification = null)
    {
        $queue = clone $queue;

        list($connection, $channel) = $this->lazyConnect($queue);

        /**
         * @param \PhpAmqpLib\Message\AMQPMessage $rabbitMQMessage
         */
        $closure = function ($rabbitMQMessage) use ($onReceive, $onError, $queue) {
            $message = new Message($rabbitMQMessage->body, $queue);
            $message->withHeaders(array_merge($rabbitMQMessage->get_properties(), [
                "content_type" => $rabbitMQMessage->getContentEncoding(),
                "consumer_tag" => $rabbitMQMessage->getConsumerTag(),
            ]));

            try {
                $result = $onReceive($message);
                if (!is_null($result) && (($result & Message::NACK) == Message::NACK)) {
                    echo "NACK\n";
                    echo ($result & Message::REQUEUE) == Message::REQUEUE ? "REQUEUE\n" : "NO REQUEUE\n";
                    $rabbitMQMessage->nack(($result & Message::REQUEUE) == Message::REQUEUE);
                } else {
                    echo "ACK\n";
                    $rabbitMQMessage->ack();
                }

                if (($result & Message::EXIT) == Message::EXIT) {
                    $rabbitMQMessage->getChannel()->basic_cancel($rabbitMQMessage->getConsumerTag());
                }
            } catch (\Exception | \Error $ex) {
                $result = $onError($message, $ex);
                if (!is_null($result) && (($result & Message::NACK) == Message::NACK)) {
                    $rabbitMQMessage->nack(($result & Message::REQUEUE) == Message::REQUEUE);
                } else {
                    $rabbitMQMessage->ack();
                }

                if (($result & Message::EXIT) == Message::EXIT) {
                    $rabbitMQMessage->getChannel()->basic_cancel($rabbitMQMessage->getConsumerTag());
                }
            }
        };

        /*
            queue: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */
        $channel->basic_consume($queue->getName(), $identification ?? $queue->getName(), false, false, false, false, $closure);

        register_shutdown_function(function () use ($channel, $connection) {
            $channel->close();
            $connection->close();
        });

        // Loop as long as the channel has callbacks registered
        $channel->consume();

    }

}

