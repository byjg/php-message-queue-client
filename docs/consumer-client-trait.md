---
sidebar_position: 7
---

# Consumer Client Trait

The `ConsumerClientTrait` is a helper to implement a consumer client. It implements the `ConsumerClientInterface`.

:::tip
This trait simplifies the creation of consumer clients by providing a standard consume() method. You only need to define the connector, pipe, and message processing logic.
:::

```php
<?php
use ByJG\MessageQueueClient\ConsumerClientInterface;
use ByJG\MessageQueueClient\ConsumerClientTrait;
use ByJG\MessageQueueClient\Connector\ConnectorInterface;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MyConsumerClient implements ConsumerClientInterface
{
    use ConsumerClientTrait;
    
    protected ConnectorInterface $connector;
    protected Pipe $pipe;

    public function __construct(ConnectorInterface $connector, Pipe $pipe)
    {
        $this->connector = $connector;
        $this->pipe = $pipe;
    }
    
    // From the Interface
    public function getPipe(): Pipe
    {
        return $this->pipe;
    }

    public function getConnector(): ConnectorInterface
    {
        return $this->connector;
    }

    public function getLogger(): LoggerInterface
    {
        return new NullLogger();
    }

    public function getLogOutputStart(Message $message): string
    {
        return "Start";
    }

    public function getLogOutputException(\Throwable $exception, Message $message): string
    {
        return "Exception: " . $exception->getMessage();
    }

    public function getLogOutputSuccess(Message $message): string
    {
        return "Success";
    }
    
    public function processMessage(Message $message): void
    {
        echo "Process the message";
        echo $message->getBody();
    }
}
```

## Required Methods

:::important
You must implement all methods from the `ConsumerClientInterface`:

- `getPipe()`: Return the Pipe to consume from
- `getConnector()`: Return the ConnectorInterface instance
- `getLogger()`: Return a PSR-3 LoggerInterface instance
- `getLogOutputStart()`: Return log message when processing starts
- `getLogOutputException()`: Return log message when an exception occurs
- `getLogOutputSuccess()`: Return log message when processing succeeds
- `processMessage()`: Implement your message processing logic
:::

## Publish

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\Envelope;
use ByJG\MessageQueueClient\Message;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;

$consumerClient = new MyConsumerClient($connector, $pipe);

// Create a message
$message = new Message("Hello World");

// Publish the message into the queue
$consumerClient->getConnector()->publish(
    new Envelope($consumerClient->getPipe(), $message)
);
```

## Consume

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\Connector\Pipe;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;

// Register the connector and associate with a scheme
ConnectorFactory::registerConnector(MockConnector::class);

// Create a connector
$connector = ConnectorFactory::create(new Uri("mock://local"));

// Create a queue
$pipe = new Pipe("test");

// Create a consumer client
$consumerClient = new MyConsumerClient($connector, $pipe);

// Connect to the queue and wait to consume the message
$consumerClient->consume();
```

----
[Open source ByJG](http://opensource.byjg.com)
