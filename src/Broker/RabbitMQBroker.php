<?php

namespace ByJG\MessagingClient\Broker;

use ByJG\MessagingClient\Message\Message;
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

    protected function lazyConnect($queue, $topic, $properties)
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

        $channel = $connection->channel();

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, false);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($topic, $properties['exchange_type'], false, true, false);

        $channel->queue_bind($queue, $topic, $queue);

        return [$connection, $channel];
    }


    public function publish(Message $rabbitMQMessage)
    {

        $topic = $rabbitMQMessage->getQueue()->getTopic() ?? $rabbitMQMessage->getQueue()->getName();
        $queue = $rabbitMQMessage->getQueue()->getName();
        $headers = $rabbitMQMessage->getHeaders();
        $headers['content_type'] = $headers['content_type'] ?? 'text/plain';
        $headers['delivery_mode'] = $headers['delivery_mode'] ?? AMQPMessage::DELIVERY_MODE_PERSISTENT;

        $properties = $rabbitMQMessage->getQueue()->getProperties();
        $properties['exchange_type'] = $properties['exchange_type'] ?? AMQPExchangeType::DIRECT;

        list($connection, $channel) = $this->lazyConnect($queue, $topic, $properties);

        $rabbitMQMessageBody = $rabbitMQMessage->getBody();

        $rabbitMQMessage = new AMQPMessage($rabbitMQMessageBody, $headers);

        $channel->basic_publish($rabbitMQMessage, $topic, $queue);

        $channel->close();
        $connection->close();
    }

    public function consume(Queue $queue, \Closure $onReceive, \Closure $onError, $identification = null)
    {
        $exchange = $queue->getTopic() ?? $queue->getName();

        $properties = $queue->getProperties();
        $properties['exchange_type'] = $properties['exchange_type'] ?? AMQPExchangeType::DIRECT;

        list($connection, $channel) = $this->lazyConnect($queue->getName(), $exchange, $properties);

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
                if ($onReceive($message)) {
                    $rabbitMQMessage->ack();
                } else {
                    $rabbitMQMessage->nack();
                }
            } catch (\Exception | \Error $ex) {
                if ($onError($message, $ex)) {
                    $rabbitMQMessage->ack();
                } else {
                    $rabbitMQMessage->nack();
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

        register_shutdown_function(function () use ($channel, $connection){
            $channel->close();
            $connection->close();
        });

        // Loop as long as the channel has callbacks registered
        $channel->consume();

    }

}

