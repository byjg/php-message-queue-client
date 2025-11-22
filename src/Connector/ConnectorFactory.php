<?php

namespace ByJG\MessageQueueClient\Connector;

use ByJG\MessageQueueClient\Exception\InvalidClassException;
use ByJG\MessageQueueClient\Exception\ProtocolNotRegisteredException;
use ByJG\Util\Uri;

class ConnectorFactory
{
    private static array $config = [];

    /**
     * @param string $class
     * @return void
     * @throws InvalidClassException
     */
    public static function registerConnector(string $class): void
    {
        if (!in_array(ConnectorInterface::class, class_implements($class) ?: [])) {
            throw new InvalidClassException('Class not implements ConnectorInterface!');
        }

        /** @var ConnectorInterface $class */
        $protocolList = $class::schema();
        foreach ($protocolList as $item) {
            self::$config[$item] = $class;
        }
    }

    /**
     * @param string|Uri $connection
     * @return ConnectorInterface
     * @throws ProtocolNotRegisteredException
     */
    public static function create(Uri|string $connection): ConnectorInterface
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
        /** @var ConnectorInterface $object */
        $object = new $class($uri);
        $object->setUp($uri);

        return $object;
    }
}