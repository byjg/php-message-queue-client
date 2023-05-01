<?php

namespace ByJG\MessagingClient\Broker;

use ByJG\MessagingClient\Exception\InvalidClassException;
use ByJG\MessagingClient\Exception\ProtocolNotRegisteredException;
use ByJG\Util\Uri;

class BrokerFactory
{
    private static $config = [];

    /**
     * @param string $protocol
     * @param string $class
     * @return void
     */
    public static function registerBroker($protocol, $class)
    {
        if (!class_exists($class, true)) {
            throw new InvalidClassException('Class not found!');
        }
        self::$config[$protocol] = $class;
    }

    /**
     * @param Uri|string $connection
     * @return BrokerInterface
     */
    public static function create($connection): BrokerInterface
    {
        if ($connection instanceof Uri) {
            $uri = $connection;
        } else {
            $uri = new Uri($connection);
        }

        if (!isset(self::$config[$uri->getScheme()])) {
            throw new ProtocolNotRegisteredException('Protocol not found/registered!');
        }

        $class = self::$config[$uri->getScheme()];
        $object = new $class($uri);
        $object->setUp($uri);

        return $object;
    }
}