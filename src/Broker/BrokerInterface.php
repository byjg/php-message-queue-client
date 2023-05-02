<?php

namespace ByJG\MessagingClient\Broker;

use ByJG\MessagingClient\Message\Message;
use ByJG\Util\Uri;

interface BrokerInterface
{
    public function setUp(Uri $uri);

    public function getConnection();

    public function publish(Message $message);

    public function consume(Queue $queue, \Closure $onReceive, \Closure $onError, $identification = null);
}
