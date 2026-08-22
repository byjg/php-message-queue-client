# Changelog - Version 6.0

## Overview

Version 6.0 is a major release that brings PHP 8.4 compatibility, modernizes dependencies, and includes important enhancements to the testing and development workflow. This release focuses on improving developer experience and maintaining compatibility with the latest PHP versions.

## New Features

### PHP 8.4 Compatibility
- Added full support for PHP 8.4, ensuring the library works with the latest PHP version
- Added `#[\Override]` attributes to all interface implementations for better type safety and clarity
- Updated minimum PHP version to 8.3 (dropped support for PHP 8.1 and 8.2)

### Development Workflow Enhancements
- Added GitPod configuration (`.gitpod.yml`) for cloud-based development environment
- Added VSCode launch configurations for improved debugging experience (`.vscode/launch.json`)
- Added Psalm run configuration for PhpStorm/IntelliJ IDEA (`.run/psalm.run.xml`)
- Added composer scripts for common tasks:
  - `composer test` - Run PHPUnit tests
  - `composer psalm` - Run Psalm static analysis

### Enhanced Documentation
- Added comprehensive documentation for core components:
  - [Connector Factory](docs/connector-factory.md) - Complete guide for creating connector instances
  - [Connector Interface](docs/connector-interface.md) - Detailed interface documentation
  - [Envelope](docs/envelope.md) - Message envelope documentation
  - [Message](docs/message.md) - Message class documentation
  - [Pipe](docs/pipe.md) - Queue/topic abstraction documentation
- Expanded [Consumer Client Trait](docs/consumer-client-trait.md) documentation
- Expanded [Mock Connector](docs/mock-connector.md) documentation
- Updated README with clearer architecture diagrams and component descriptions

### Testing Improvements
- Enhanced test fixtures with better logging capabilities
- Added `LoggerAssert` fixture for testing logging behavior
- Improved PHPUnit workflow to use root privileges for better Docker integration
- Updated GitHub Actions workflow to use actions/checkout@v5

## Bug Fixes

### Psalm Compatibility
- Fixed null coalescing issue in `ConnectorFactory::registerConnector()` to handle `class_implements()` returning false
- Added proper type annotations to satisfy Psalm 6.x static analysis
- Refactored Psalm configuration for better compatibility with modern versions

### Logger Error Handling
- Improved logger error handling in `ConsumerClientTrait`
- Adjusted log level handling for better error reporting

### Message Handling
- Fixed bit shifting operations for message flags
- Improved message binary representation handling

## Breaking Changes

| Component | Before (5.x) | After (6.0) | Description |
|-----------|-------------|-------------|-------------|
| PHP Version | `>=8.1 <8.4` | `>=8.3 <8.6` | Minimum PHP version increased from 8.1 to 8.3. Support added for PHP 8.4 and 8.5. |
| byjg/uri | `^5.0` | `^6.0` | Dependency upgraded to version 6.0 |
| PHPUnit | `^9.6` | `^10.5\|^11.5` | PHPUnit upgraded to versions 10.5 or 11.5 |
| Psalm | `^5.9` | `^5.9\|^6.13` | Added support for Psalm 6.13 while maintaining backward compatibility with 5.9 |
| Override Attributes | Not present | Added `#[\Override]` | All interface method implementations now use the `#[\Override]` attribute (requires PHP 8.3+) |

## Path to Upgrade from 5.x to 6.0

### Step 1: Update PHP Version
Ensure your environment is running PHP 8.3 or higher:
```bash
php -v
```

If you're running PHP 8.1 or 8.2, you'll need to upgrade to PHP 8.3, 8.4, or 8.5.

### Step 2: Update Composer Dependencies
Update your `composer.json` to require version 6.0:
```json
{
    "require": {
        "byjg/message-queue-client": "^6.0"
    }
}
```

### Step 3: Run Composer Update
```bash
composer update byjg/message-queue-client
```

This will also update the `byjg/uri` dependency to version 6.0.

### Step 4: Update Your Connector Implementations (if applicable)
If you have custom connector implementations:

1. Add `#[\Override]` attributes to all methods that implement `ConnectorInterface`:
```php
// Before (5.x)
public function setUp(Uri $uri): void
{
    // ...
}

// After (6.0)
#[\Override]
public function setUp(Uri $uri): void
{
    // ...
}
```

2. Add the attribute to these methods:
   - `schema()`
   - `setUp()`
   - `getDriver()`
   - `publish()`
   - `consume()`

### Step 5: Update Your Consumer Client Implementations (if applicable)
If you implement `ConsumerClientInterface`, add `#[\Override]` attributes to all interface methods:
- `getPipe()`
- `getConnector()`
- `getLogger()`
- `getLogOutputStart()`
- `getLogOutputException()`
- `getLogOutputSuccess()`
- `processMessage()`

### Step 6: Update Development Dependencies (if applicable)
If you're running tests or static analysis, update your dev dependencies:

```bash
# Update PHPUnit (if you're using it for testing)
composer require --dev phpunit/phpunit:"^10.5|^11.5"

# Update Psalm (if you're using it for static analysis)
composer require --dev vimeo/psalm:"^5.9|^6.13"
```

### Step 7: Run Tests
After upgrading, run your test suite to ensure everything works correctly:
```bash
composer test
# or
vendor/bin/phpunit
```

### Step 8: Run Static Analysis
If you use Psalm, run it to check for any type issues:
```bash
composer psalm
# or
vendor/bin/psalm
```

## Notes

- The upgrade is relatively straightforward if you're only using the library without custom implementations
- Custom connector or consumer implementations will require adding `#[\Override]` attributes
- The library maintains backward compatibility in terms of functionality - only the PHP version requirement and attribute syntax have changed
- All existing message queue operations (publish/consume) work exactly the same way as in version 5.x

## Contributors

Special thanks to @HilarioJrx for contributions to error logging and message handling improvements.
