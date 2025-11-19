---
sidebar_position: 2
---

# Message Class

The `Message` class represents a message that can be published to or consumed from a message queue.

:::info
Messages can contain any type of data in their body: strings, JSON, objects, or serialized data.
:::

## Basic Usage

```php
<?php
use ByJG\MessageQueueClient\Message;

// Create a simple message with a string body
$message = new Message("Hello World");

// Create a message with a JSON body
$message = new Message(json_encode([
    "id" => 1,
    "name" => "John Doe"
]));

// Create a message with an object body
$object = new stdClass();
$object->id = 1;
$object->name = "John Doe";
$message = new Message($object);
```

## Properties

You can add properties to the message for additional metadata:

```php
<?php
// Adding properties to the message
$message->withProperty("content-type", "application/json")
        ->withProperty("timestamp", time());

// Or replace all properties at once
$message->withProperties([
    "content-type" => "application/json",
    "timestamp" => time()
]);

// Get all properties
$allProperties = $message->getProperties();
```

## Response Constants

:::important
When consuming messages, your callback **must** return one of these constants to indicate how the message should be handled.
:::

```php
<?php
// Constants used when consuming messages
Message::ACK;     // Acknowledge message (processed successfully)
Message::NACK;    // Not acknowledge (failed processing, move to DLQ if configured)
Message::REQUEUE; // Put the message back in the queue
Message::EXIT;    // Exit the consume loop

// You can combine EXIT with other constants
return Message::ACK | Message::EXIT; // Acknowledge and exit
```

## Methods Reference

| Method                                               | Description                               |
|------------------------------------------------------|-------------------------------------------|
| `__construct(mixed $body)`                           | Creates a new message with the given body |
| `getBody(): mixed`                                   | Returns the body of the message             |
| `getProperties(): array`                             | Gets all properties                         |
| `withProperty(string $property, mixed $value): self` | Adds a property to the message              |
| `withProperties(array $properties): self`            | Replaces all properties with the given array |

## Constants Reference

| Constant  | Binary Value | Description                                             |
|-----------|--------------|---------------------------------------------------------|
| `ACK`     | 0b0001       | Acknowledge the message and remove from the queue       |
| `NACK`    | 0b0010       | Not acknowledge the message and potentially move to DLQ |
| `REQUEUE` | 0b0100       | Requeue the message                                     |
| `EXIT`    | 0b1000       | Exit the consume method                                 |

----
[Open source ByJG](http://opensource.byjg.com) 