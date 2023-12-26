<?php

namespace ByJG\MessageQueueClient;

use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;

class MockConnector implements ConnectorInterface
{
    public static $mockedConnections = [];

    public static function schema()
    {
        return ["mock"];
    }


    /** @var \ByJG\Util\Uri */
    protected $uri;

    public function setUp(\ByJG\Util\Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        $hash = md5(trim(strval($this->uri), "/"));
        if (!isset(self::$mockedConnections[$hash])) {
            self::$mockedConnections[$hash] = [];
        }
        return $hash;
    }

    public function publish(Envelope $envelope)
    {
        self::$mockedConnections[$this->getConnection()][$envelope->getPipe()->getName()][] = $envelope;
    }

    public function consume(Pipe $pipe, \Closure $onReceive, \Closure $onError, $identification = null): void
    {
        $pipe = clone $pipe;

        $envelope = array_shift(self::$mockedConnections[$this->getConnection()][$pipe->getName()]);
        try {
            $onReceive($envelope);
        } catch (\Exception | \Error $ex) {
            $onError($envelope, $ex);
            throw $ex;
        }
    }

}

