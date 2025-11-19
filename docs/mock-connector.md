---
sidebar_position: 6
---

# Mock Connector

The Mock Connector is a connector that simulates a message queue for testing purposes without requiring an actual message queue server setup.

:::tip Testing
The Mock Connector stores messages in memory using a static array and provides a simplified way to test message queue functionality without external dependencies.
:::

## Usage

### Instantiate the Connector

```php
<?php
use ByJG\MessageQueueClient\Connector\ConnectorFactory;
use ByJG\MessageQueueClient\MockConnector;
use ByJG\Util\Uri;

// Register the connector and associate with a scheme
ConnectorFactory::registerConnector(MockConnector::class);

// Create a connector
$connector = ConnectorFactory::create(new Uri("mock://local"));
```

### Publish & Consume

:::note
The usage is the same as other connectors. See the main documentation for basic publish and consume examples.
:::

### How it Works Internally

The MockConnector:
- Stores messages in a static array (`$mockedConnections`) keyed by a hash of the connection URI
- When publishing, adds the envelope to this array under the appropriate pipe name
- When consuming, removes the first message from the array and passes it to the callback function
- If an exception occurs during callback processing, it calls the error callback and re-throws the exception

### Protocol Information

| Protocol | URI Example  | Notes                                                     |
|----------|--------------|-----------------------------------------------------------|
| mock     | mock://local | This emulates a pub/sub consumer. It is for use on tests. |

----
[Open source ByJG](http://opensource.byjg.com)
