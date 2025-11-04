---
sidebar_position: 1
---

# Pipe Class

The `Pipe` class represents a message queue or topic in the messaging system. It contains the name of the queue and optional properties associated with it.

:::info
The Pipe class is a fundamental component that defines where messages will be published to or consumed from.
:::

## Basic Usage

```php
<?php
use ByJG\MessageQueueClient\Connector\Pipe;

// Create a basic pipe (queue)
$pipe = new Pipe("my-queue");

// Create a pipe with a dead letter queue
$deadLetterPipe = new Pipe("dlq-my-queue");
$pipe->withDeadLetter($deadLetterPipe);
```

## Properties

You can add properties to the pipe for connector-specific configurations:

```php
<?php
// Adding properties to the pipe
$pipe->withProperty("durable", true)
     ->withProperty("auto_delete", false);

// Or replace all properties at once
$pipe->withProperties([
    "durable" => true,
    "auto_delete" => false
]);

// Get a specific property
$durable = $pipe->getProperty("durable", false); // Second parameter is the default value

// Get all properties
$allProperties = $pipe->getProperties();
```

## Dead Letter Queues

:::tip
A dead letter queue is a pipe where messages that cannot be processed are sent to. This is useful for handling failed messages without losing them.
:::

```php
<?php
// Create the main pipe
$pipe = new Pipe("main-queue");

// Create a dead letter pipe
$deadLetterPipe = new Pipe("dead-letter-queue");

// Associate the dead letter pipe with the main pipe
$pipe->withDeadLetter($deadLetterPipe);

// Later, you can get the dead letter pipe
$dlq = $pipe->getDeadLetter();
```

## Methods Reference

| Method                                                        | Description                                  |
|---------------------------------------------------------------|----------------------------------------------|
| `__construct(string $pipe)`                                   | Creates a new pipe with the given name       |
| `getName(): string`                                           | Returns the name of the pipe                 |
| `withProperty(string $property, mixed $value): self`          | Adds a property to the pipe                  |
| `withProperties(array $properties): self`                     | Replaces all properties with the given array |
| `setProperty(string $property, mixed $value): self`           | Sets a property (alias for withProperty)     |
| `setPropertyIfNull(string $property, mixed $value): self`     | Sets a property only if it doesn't exist     |
| `deleteProperty(string $property): self`                      | Removes a property from the pipe             |
| `getProperty(string $property, mixed $default = null): mixed` | Gets a property value or returns the default |
| `getProperties(): array`                                      | Gets all properties                          |
| `getDeadLetter(): ?Pipe`                                      | Gets the dead letter pipe if defined         |
| `withDeadLetter(Pipe $deadLetter): self`                      | Associates a dead letter pipe                |

----
[Open source ByJG](http://opensource.byjg.com) 