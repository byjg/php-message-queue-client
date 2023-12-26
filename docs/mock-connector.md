# Mock Connector

The Mock Connector is a connector that doesn't enqueue the message but instead it calls the callback function directly.

It is ideal for testing purposes and doesn't require any extra configuration or installation.

## Usage

### Instantiate the Connector

```php
<?php
// Register the connector and associate with a scheme
ConnectorFactory::registerConnector(MockConnector::class);

// Create a connector
$connector = ConnectorFactory::create(new Uri("mock://local"));
```

### Public & Consume

It is the same as the other connectors. See the [README.md](../README.md) for more information.

Protocols:

| Protocol | URI Example  | Notes                                                     |
|----------|--------------|-----------------------------------------------------------|
| mock     | mock://local | This emulates a pub/sub consumer. It is for use on tests. |

----
[Open source ByJG](http://opensource.byjg.com)
