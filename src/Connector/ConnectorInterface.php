<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Envelope;
use ByJG\Util\Uri;

interface ConnectorInterface
{
    public static function schema();

    public function setUp(Uri $uri);

    public function getDriver();

    public function publish(Envelope $envelope);

    public function consume(Pipe $pipe, \Closure $onReceive, \Closure $onError, $identification = null);
}
