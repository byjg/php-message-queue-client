---
sidebar_position: 5
---

# Connector Factory

The `ConnectorFactory` is a static class that manages connector registrations and creates connector instances based on URI schemes.

## Registering Connectors

Before using a connector, you must register it with the factory:

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\MockConnector;

// Register the connector class
ConnectorFactory::registerConnector(MockConnector::class);
```

When registering a connector, the factory calls the connector's `schema()` method to determine which URI schemes it supports.

## Creating Connector Instances

To create a connector instance, use the `create()` method with a URI:

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\Util\Uri;

// Create using a string URI
$connector = ConnectorFactory::create("mock://local");

// Or create using a Uri object
$uri = new Uri("mock://local");
$connector = ConnectorFactory::create($uri);
```

The factory determines which connector to use based on the URI scheme (the part before `://`), and then:

1. Instantiates the appropriate connector class
2. Calls the connector's `setUp()` method with the URI object
3. Returns the configured connector instance

## Example with Different Connectors

You can register multiple connectors and use them based on their URI schemes:

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;

// Register different connectors
ConnectorFactory::registerConnector(MockConnector::class);                // Supports "mock://"
ConnectorFactory::registerConnector(RabbitMQConnector::class);           // Supports "rabbitmq://" or "amqp://"
ConnectorFactory::registerConnector(RedisConnector::class);              // Supports "redis://"

// Create connectors based on URI scheme
$mockConnector = ConnectorFactory::create("mock://local");
$rabbitConnector = ConnectorFactory::create("rabbitmq://username:password@localhost:5672/vhost");
$redisConnector = ConnectorFactory::create("redis://localhost:6379/0");
```

## Error Handling

The factory will throw exceptions in these cases:

- `InvalidClassException`: If the registered connector class doesn't implement `ConnectorInterface`
- `ProtocolNotRegisteredException`: If you try to create a connector for a URI scheme that hasn't been registered

```php
<?php
try {
    // This will throw ProtocolNotRegisteredException if "sqs" connector isn't registered
    $connector = ConnectorFactory::create("sqs://access:secret@region/queue");
} catch (ProtocolNotRegisteredException $e) {
    echo "No connector registered for this protocol: " . $e->getMessage();
} catch (InvalidClassException $e) {
    echo "Invalid connector class: " . $e->getMessage();
}
```

## Methods Reference

| Method                                                | Description                                                  |
|-------------------------------------------------------|--------------------------------------------------------------|
| `registerConnector(string $class): void`              | Registers a connector class                                  |
| `create(Uri\|string $connection): ConnectorInterface` | Creates and configures a connector instance based on the URI |

----
[Open source ByJG](http://opensource.byjg.com) 