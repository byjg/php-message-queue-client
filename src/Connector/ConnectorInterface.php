<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Envelope;
use ByJG\Util\Uri;

interface ConnectorInterface
{
    public static function schema();

    public function setUp(Uri $uri);

    public function getConnection();

    public function publish(Envelope $envelope);

    public function consume(Queue $queue, \Closure $onReceive, \Closure $onError, $identification = null);
}
