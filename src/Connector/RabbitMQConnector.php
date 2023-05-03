<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQConnector implements ConnectorInterface
{
    const ROUTING_KEY = '_x_routing_key';
    const EXCHANGE = '_x_exchange';

    const PARAM_CAPATH = 'capath';

    public static function schema()
    {
        return ["amqp", "amqps"];
    }

    /** @var \ByJG\Util\Uri */
    protected $uri;

    public function setUp(\ByJG\Util\Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection|\PhpAmqpLib\Connection\AMQPSSLConnection
     */
    public function getConnection()
    {
        $vhost = trim($this->uri->getPath(), "/");
        if (empty($vhost)) {
            $vhost = "/";
        }

        $args = [];
        if (!empty($this->uri->getQuery())) {
            parse_str($this->uri->getQuery(), $args);
        }

        if ($this->uri->getScheme() == "amqps") {
            $port = 5671;
            if (empty($args[self::PARAM_CAPATH])) {
                throw new \InvalidArgumentException("The 'capath' parameter is required for AMQPS");
            }

            $connection = new AMQPSSLConnection(
                $this->uri->getHost(),
                empty($this->uri->getPort()) ? $port : $this->uri->getPort(),
                $this->uri->getUsername(),
                $this->uri->getPassword(),
                $vhost,
                [
                    "ssl" => $args
                ]
            );
        } else {
            $port = 5672;

            $connection = new AMQPStreamConnection(
                $this->uri->getHost(),
                empty($this->uri->getPort()) ? $port : $this->uri->getPort(),
                $this->uri->getUsername(),
                $this->uri->getPassword(),
                $vhost
            );
        }


        return $connection;
    }

    /**
     * @param AMQPStreamConnection|AMQPSSLConnection $connection
     * @param Pipe $pipe
     * @return AMQPChannel
     */
    protected function createQueue($connection, Pipe &$pipe)
    {
        $pipe->setPropertyIfNull('exchange_type', AMQPExchangeType::DIRECT);
        $pipe->setPropertyIfNull(self::EXCHANGE, $pipe->getName());
        $pipe->setPropertyIfNull(self::ROUTING_KEY, $pipe->getName());

        $amqpTable = [];
        $dlq = $pipe->getDeadLetter();
        if (!empty($dlq)) {
            $dlq->withProperty('exchange_type', AMQPExchangeType::FANOUT);
            $channelDlq = $this->createQueue($connection, $dlq);
            $channelDlq->close();

            $dlqProperties = $dlq->getProperties();
            $dlqProperties['x-dead-letter-exchange'] = $dlq->getProperty(self::EXCHANGE, $dlq->getName());
            // $dlqProperties['x-dead-letter-routing-key'] = $routingKey;
            $dlqProperties['x-message-ttl'] = $dlq->getProperty('x-message-ttl', 3600 * 72*1000);
            $dlqProperties['x-expires'] = $dlq->getProperty('x-expires', 3600 * 72*1000 + 1000);
            $amqpTable = new AMQPTable($dlqProperties);
        }

        $channel = $connection->channel();

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($pipe->getName(), false, true, false, false, false, $amqpTable);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $channel->exchange_declare($pipe->getProperty(self::EXCHANGE, $pipe->getName()), $pipe->getProperty('exchange_type'), false, true, false);

        $channel->queue_bind($pipe->getName(), $pipe->getProperty(self::EXCHANGE, $pipe->getName()), $pipe->getProperty(self::ROUTING_KEY, $pipe->getName()));

        return $channel;
    }

    protected function lazyConnect(Pipe &$pipe)
    {
        $connection = $this->getConnection();
        $channel = $this->createQueue($connection, $pipe);

        return [$connection, $channel];
    }


    public function publish(Envelope $envelope)
    {
        $headers = $envelope->getMessage()->getHeaders();
        $headers['content_type'] = $headers['content_type'] ?? 'text/plain';
        $headers['delivery_mode'] = $headers['delivery_mode'] ?? AMQPMessage::DELIVERY_MODE_PERSISTENT;

        $pipe = clone $envelope->getPipe();

        list($connection, $channel) = $this->lazyConnect($pipe);

        $rabbitMQMessageBody = $envelope->getMessage()->getBody();

        $rabbitMQMessage = new AMQPMessage($rabbitMQMessageBody, $headers);

        $channel->basic_publish($rabbitMQMessage, $pipe->getProperty(self::EXCHANGE, $pipe->getName()), $pipe->getName());

        $channel->close();
        $connection->close();
    }

    public function consume(Pipe $pipe, \Closure $onReceive, \Closure $onError, $identification = null)
    {
        $pipe = clone $pipe;

        list($connection, $channel) = $this->lazyConnect($pipe);

        /**
         * @param \PhpAmqpLib\Message\AMQPMessage $rabbitMQMessage
         */
        $closure = function ($rabbitMQMessage) use ($onReceive, $onError, $pipe) {
            $message = new Message($rabbitMQMessage->body);
            $message->withHeaders($rabbitMQMessage->get_properties());
            $message->withHeader('consumer_tag', $rabbitMQMessage->getConsumerTag());
            $message->withHeader('delivery_tag', $rabbitMQMessage->getDeliveryTag());
            $message->withHeader('redelivered', $rabbitMQMessage->isRedelivered());
            $message->withHeader('exchange', $rabbitMQMessage->getExchange());
            $message->withHeader('routing_key', $rabbitMQMessage->getRoutingKey());
            $message->withHeader('body_size', $rabbitMQMessage->getBodySize());
            $message->withHeader('message_count', $rabbitMQMessage->getMessageCount());

            $envelope = new Envelope($pipe, $message);

            try {
                $result = $onReceive($envelope);
                if (!is_null($result) && (($result & Message::NACK) == Message::NACK)) {
                    // echo "NACK\n";
                    // echo ($result & Message::REQUEUE) == Message::REQUEUE ? "REQUEUE\n" : "NO REQUEUE\n";
                    $rabbitMQMessage->nack(($result & Message::REQUEUE) == Message::REQUEUE);
                } else {
                    // echo "ACK\n";
                    $rabbitMQMessage->ack();
                }

                if (($result & Message::EXIT) == Message::EXIT) {
                    $rabbitMQMessage->getChannel()->basic_cancel($rabbitMQMessage->getConsumerTag());
                    $currentConnection = $rabbitMQMessage->getChannel()->getConnection();
                    $rabbitMQMessage->getChannel()->close();
                    $currentConnection->close();
                }
            } catch (\Exception | \Error $ex) {
                $result = $onError($envelope, $ex);
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
            pipe: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */
        $channel->basic_consume($pipe->getName(), $identification ?? $pipe->getName(), false, false, false, false, $closure);

        register_shutdown_function(function () use ($channel, $connection) {
            $channel->close();
            $connection->close();
        });

        // Loop as long as the channel has callbacks registered
        $channel->consume();

    }

}

