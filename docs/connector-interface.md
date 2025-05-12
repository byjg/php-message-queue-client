---
sidebar_position: 4
---

# Connector Interface

The `ConnectorInterface` defines the methods that all message queue connectors must implement. This interface allows the library to work with different message queue systems in a standardized way.

## Interface Methods

```php
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

    public function consume(Pipe $pipe, Closure $onReceive, Closure $onError, ?string $identification = null): void;
}
```

## Method Explanations

### `schema(): array`

This static method returns an array of strings representing the URI schemes that this connector supports.

```php
<?php
// Example implementation
public static function schema(): array
{
    return ["rabbitmq", "amqp"];
}
```

### `setUp(Uri $uri): void`

This method configures the connector with the connection URI. The URI contains all the necessary information to connect to the message queue service.

```php
<?php
// Example implementation
public function setUp(Uri $uri): void
{
    $this->host = $uri->getHost();
    $this->port = $uri->getPort() ?: 5672;
    $this->username = $uri->getUsername();
    $this->password = $uri->getPassword();
    $this->vhost = $uri->getPath() ?: "/";
}
```

### `getDriver(): mixed`

This method returns the driver or client instance for the message queue service. This is often a client library specific to the message queue system.

```php
<?php
// Example implementation
public function getDriver(): mixed
{
    if ($this->connection === null) {
        // Create connection to the message queue service
        $this->connection = new SomeMessageQueueClient($this->host, $this->port);
        $this->connection->login($this->username, $this->password);
    }
    
    return $this->connection;
}
```

### `publish(Envelope $envelope): void`

This method publishes a message to a queue or topic.

```php
<?php
// Example implementation
public function publish(Envelope $envelope): void
{
    $driver = $this->getDriver();
    
    $pipe = $envelope->getPipe();
    $message = $envelope->getMessage();
    
    $driver->basicPublish(
        $message->getBody(),
        $pipe->getName(),
        $message->getProperties()
    );
}
```

### `consume(Pipe $pipe, Closure $onReceive, Closure $onError, ?string $identification = null): void`

This method consumes messages from a queue, calling the provided callbacks to process messages or handle errors.

```php
<?php
// Example implementation
public function consume(Pipe $pipe, Closure $onReceive, Closure $onError, ?string $identification = null): void
{
    $driver = $this->getDriver();
    
    $driver->basicConsume(
        $pipe->getName(),
        $identification ?: "consumer",
        function ($receivedMessage) use ($onReceive, $onError, $pipe) {
            try {
                $message = new Message($receivedMessage->body);
                $message->withProperties($receivedMessage->properties);
                
                $envelope = new Envelope($pipe, $message);
                
                $result = $onReceive($envelope);
                
                if ($result & Message::ACK) {
                    $receivedMessage->ack();
                } elseif ($result & Message::NACK) {
                    $receivedMessage->nack();
                } elseif ($result & Message::REQUEUE) {
                    $receivedMessage->nack(true);
                }
                
                if ($result & Message::EXIT) {
                    return false; // Stop consuming
                }
                
                return true; // Continue consuming
            } catch (\Throwable $ex) {
                $response = $onError($envelope, $ex);
                
                // Handle the response (ACK, NACK, REQUEUE, EXIT)
                // Similar to above
                
                return !($response & Message::EXIT);
            }
        }
    );
    
    // Start consuming loop
    $driver->wait();
}
```

## Implementing a Connector

To implement a new connector:

1. Create a class that implements `ConnectorInterface`
2. Register it with the `ConnectorFactory`:

```php
<?php
ConnectorFactory::registerConnector(MyCustomConnector::class);
```

3. Use it with your message queue URI scheme:

```php
<?php
$connector = ConnectorFactory::create("mycustom://localhost:1234");
```

----
[Open source ByJG](http://opensource.byjg.com) 