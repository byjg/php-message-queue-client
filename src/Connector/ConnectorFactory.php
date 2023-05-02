<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Exception\InvalidClassException;
use ByJG\MessageQueueClient\Exception\ProtocolNotRegisteredException;
use ByJG\Util\Uri;

class ConnectorFactory
{
    private static $config = [];

    /**
     * @param string $protocol
     * @param string $class
     * @return void
     */
    public static function registerConnector($class)
    {
        if (!in_array(ConnectorInterface::class, class_implements($class))) {
            throw new InvalidClassException('Class not implements ConnectorInterface!');
        }

        $protocolList = $class::schema();
        foreach ((array)$protocolList as $item) {
            self::$config[$item] = $class;
        }
    }

    /**
     * @param Uri|string $connection
     * @return ConnectorInterface
     */
    public static function create($connection): ConnectorInterface
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