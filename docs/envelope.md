---
sidebar_position: 3
---

# Envelope Class

The `Envelope` class encapsulates a message with its destination pipe (queue or topic). It is used to publish messages to a specific pipe.

:::info
An Envelope combines a Message with a Pipe to create a complete unit for publishing to a message queue.
:::

## Basic Usage

```php
<?php
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\Envelope;

// Create a pipe (queue)
$pipe = new Pipe("my-queue");

// Create a message
$message = new Message("Hello World");

// Create an envelope containing the message and its destination
$envelope = new Envelope($pipe, $message);

// Now you can publish this envelope
$connector->publish($envelope);
```

## Getting Envelope Components

You can retrieve the message and pipe from an envelope:

```php
<?php
// Get the message from the envelope
$message = $envelope->getMessage();

// Get the pipe from the envelope
$pipe = $envelope->getPipe();

// Access message contents
$body = $message->getBody();
$properties = $message->getProperties();

// Access pipe information
$pipeName = $pipe->getName();
$pipeProperties = $pipe->getProperties();
```

## Methods Reference

| Method                                       | Description                                            |
|----------------------------------------------|--------------------------------------------------------|
| `__construct(Pipe $pipe, Message $message)`  | Creates a new envelope with the given pipe and message |
| `getMessage(): Message`                      | Returns the message                                    |
| `getPipe(): Pipe`                            | Returns the pipe                                       |

## Use in Connector Implementation

:::note
If you are implementing a custom connector, you will receive an Envelope when publishing.
:::

```php
<?php
public function publish(Envelope $envelope): void
{
    // Get the destination pipe
    $pipe = $envelope->getPipe();
    $pipeName = $pipe->getName();
    
    // Get the message to publish
    $message = $envelope->getMessage();
    $messageBody = $message->getBody();
    $messageProperties = $message->getProperties();
    
    // Use this information to publish to your message broker
    // ...
}
```

----
[Open source ByJG](http://opensource.byjg.com) 