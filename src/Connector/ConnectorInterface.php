<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Envelope;
use ByJG\Util\Uri;
use Closure;

interface ConnectorInterface
{
    public static function schema(): array;

    public function setUp(Uri $uri): void;

    public function getDriver(): mixed;

    public function publish(Envelope $envelope): void;

    public function consume(Pipe $pipe, Closure $onReceive, Closure $onError, $identification = null): void;
}
