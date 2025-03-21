<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\Util\Uri;
use Closure;
use Error;
use Exception;

class MockConnector implements ConnectorInterface
{
    public static array $mockedConnections = [];

    #[\Override]
    public static function schema(): array
    {
        return ["mock"];
    }


    /** @var Uri */
    protected Uri $uri;

    #[\Override]
    public function setUp(Uri $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getDriver(): string
    {
        $hash = md5(trim(strval($this->uri), "/"));
        if (!isset(self::$mockedConnections[$hash])) {
            self::$mockedConnections[$hash] = [];
        }
        return $hash;
    }

    #[\Override]
    public function publish(Envelope $envelope): void
    {
        self::$mockedConnections[$this->getDriver()][$envelope->getPipe()->getName()][] = $envelope;
    }

    #[\Override]
    public function consume(Pipe $pipe, Closure $onReceive, Closure $onError, ?string $identification = null): void
    {
        $pipe = clone $pipe;

        $envelope = array_shift(self::$mockedConnections[$this->getDriver()][$pipe->getName()]);
        try {
            $onReceive($envelope);
        } catch (Exception | Error $ex) {
            $onError($envelope, $ex);
            throw $ex;
        }
    }

}

