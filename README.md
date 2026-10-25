---
sidebar_key: message-queue-client
sidebar_category: messaging
tags: [php, queue]
---

# Message Queue Client

One interface to publish and consume messages, whatever the broker behind it. Change the queue, or write a new connector, without changing your code.

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-message-queue-client/actions/workflows/phpunit.yml/badge.svg?branch=main)](https://github.com/byjg/php-message-queue-client/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-message-queue-client/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-message-queue-client.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-message-queue-client.svg)](https://github.com/byjg/php-message-queue-client/releases/)

## Why

Each broker has its own client library, its own vocabulary (exchange, stream, topic,
channel) and its own way to acknowledge or reject a message. Code written against one of
them has to be rewritten to move to another, and is hard to test without the broker
running.

This library defines the contract once. Your code publishes an `Envelope` and consumes a
`Pipe` through `ConnectorInterface`; the broker is chosen by the connection URI:

```php
$connector = ConnectorFactory::create(new Uri("amqp://user:pass@rabbit:5672/"));  // RabbitMQ
$connector = ConnectorFactory::create(new Uri("redis://redis:6379"));             // Redis
$connector = ConnectorFactory::create(new Uri("mock://local"));                   // tests

$connector->publish(new Envelope(new Pipe("orders"), new Message($payload)));
```

- **Change the broker without touching the code.** Moving from Redis to RabbitMQ is a new
  connection string and a different `composer require`.
- **Test without a broker.** The `MockConnector` keeps the messages in memory.
- **Add a broker by implementing one interface.** A connector is `publish()`, `consume()`
  and a URI scheme; see [Connector Interface](docs/connector-interface.md).

## Features

* Low code to publish and consume messages
* Messages, Queues and Connector objects are decoupled
* Easy to implement new connectors

```text
┌─────────────────┐                  ┌────────────────────────┐
│                 │                  │  Envelope              │
│                 │                  │                        │
│                 │                  │                        │
│                 │                  │   ┌─────────────────┐  │
│                 │   publish()      │   │      Pipe       │  │
│                 ├─────────────────▶│   └─────────────────┘  │
│                 │                  │   ┌─────────────────┐  │
│                 │                  │   │     Message     │  │
│                 │                  │   └─────────────────┘  │
│                 │                  │                        │
│                 │                  └────────────────────────┘
│    Connector    │
│                 │
│                 │
│                 │       consume()     ┌─────────────────┐
│                 │◀────────────────────│      Pipe       │
│                 │                     └─────────────────┘
│                 │
│                 │
│                 │
└─────────────────┘
```

## Code Structure

| Component               | Description                                                                    | Location                                                |
|-------------------------|--------------------------------------------------------------------------------|---------------------------------------------------------|
| Message                 | Represents a message payload with properties                                   | `\ByJG\MessageQueueClient\Message`                      |
| Pipe                    | Abstraction representing a queue or topic destination with optional properties | `\ByJG\MessageQueueClient\Connector\Pipe`               |
| Envelope                | Combines a Message with its destination Pipe                                   | `\ByJG\MessageQueueClient\Envelope`                     |
| ConnectorInterface      | Interface for message queue implementations                                    | `\ByJG\MessageQueueClient\Connector\ConnectorInterface` |
| ConnectorFactory        | Factory for creating connector instances                                       | `\ByJG\MessageQueueClient\Connector\ConnectorFactory`   |
| ConsumerClientTrait     | Helper for implementing consumer clients                                       | `\ByJG\MessageQueueClient\ConsumerClientTrait`          |
| ConsumerClientInterface | Interface for consumer client implementations                                  | `\ByJG\MessageQueueClient\ConsumerClientInterface`      |

## Implemented Connectors

| Connector | URL / Documentation                                                                      | Composer Package        |
|-----------|------------------------------------------------------------------------------------------|-------------------------|
| Mock      | [docs/mock-connector.md](docs/mock-connector.md)                                         | -                       |
| RabbitMQ  | [https://github.com/byjg/rabbitmq-client](https://github.com/byjg/rabbitmq-client)       | byjg/rabbitmq-client    |
| Redis     | [https://github.com/byjg/redis-queue-client](https://github.com/byjg/redis-queue-client) | byjg/redis-queue-client |

## Usage

### Publish

```php
<?php
// Register the connector and associate with a scheme
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;

ConnectorFactory::registerConnector(MockConnector::class);

// Create a connector
$connector = ConnectorFactory::create(new Uri("mock://local"));

// Create a queue
$pipe = new Pipe("test");
$pipe->withDeadLetter(new Pipe("dlq_test"));

// Create a message
$message = new Message("Hello World");

// Publish the message into the queue
$connector->publish(new Envelope($pipe, $message));
```

### Consume

```php
<?php
// Register the connector and associate with a scheme
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;

ConnectorFactory::registerConnector(MockConnector::class);

// Create a connector
$connector = ConnectorFactory::create(new Uri("mock://local"));

// Create a queue
$pipe = new Pipe("test");
$pipe->withDeadLetter(new Pipe("dlq_test"));

// Connect to the queue and wait to consume the message
$connector->consume(
    $pipe,                                 // Queue name
    function (Envelope $envelope) {         // Callback function to process the message
        echo "Process the message";
        echo $envelope->getMessage()->getBody();
        return Message::ACK;
    },
    function (Envelope $envelope, $ex) {    // Callback function to process the failed message
        echo "Process the failed message";
        echo $ex->getMessage();
        return Message::REQUEUE;
    }
);
```

The consume method will wait for a message and call the callback function to process the message.
If there is no message in the queue, the method will wait until a message arrives.

If you want to exit the consume method, just return `Message::ACK | Message::EXIT` from the callback function.

Possible return values from the callback function:

* `Message::ACK` - Acknowledge the message and remove from the queue
* `Message::NACK` - Not acknowledge the message and remove from the queue. If the queue has a dead letter queue, the message will be sent to the dead letter queue.
* `Message::REQUEUE` - Requeue the message
* `Message::EXIT` - Exit the consume method

## Consumer Client

You can simplify the consume method by using the ConsumerClientTrait. See more details in the [docs/consumer-client-trait.md](docs/consumer-client-trait.md).

## Connectors

The connectors are the classes responsible to connect to the message queue server and send/receive messages.

All connector have the following interface:

```php
<?php
interface ConnectorInterface
{
    public static function schema(): array;

    public function setUp(Uri $uri): void;

    public function getDriver(): mixed;

    public function publish(Envelope $envelope): void;

    public function consume(Pipe $pipe, \Closure $onReceive, \Closure $onError, ?string $identification = null): void;
}
```

There is no necessary call the method `getDriver()` because the method publish() and consume() will call it automatically.
Use the method `getDriver()` only if you need to access the connection directly.

## Documentation

### Core Components
- [Pipe Class](docs/pipe.md) - Represents a message queue or topic
- [Message Class](docs/message.md) - Represents a message that can be published or consumed
- [Envelope Class](docs/envelope.md) - Encapsulates a message with its destination pipe

### Connectors
- [Connector Interface](docs/connector-interface.md) - Interface for message queue connectors
- [Connector Factory](docs/connector-factory.md) - Factory for creating connector instances
- [Mock Connector](docs/mock-connector.md) - Simple connector for testing

### Helpers
- [Consumer Client Trait](docs/consumer-client-trait.md) - Helper for implementing consumer clients

## Installation

```bash
composer require "byjg/message-queue-client"
```

## Running Tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](https://opensource.byjg.com)
