# LiteCollection

A lightweight PHP library for managing data collections with a persistent storage system using SQLite.

## Features

- **Lightweight Collection**: Implements `Countable`, `ArrayAccess`, and `IteratorAggregate` interfaces
- **SQLite Storage**: Data persistence in memory or on disk
- **Two Operating Modes**:
  - `MODE_NORMAL`: Uses auto-incremented keys (0, 1, 2...)
  - `MODE_ASSOCIATIVE`: Uses a custom primary key
- **Simple Interface**: Manipulate data like a standard PHP array

## Installation

```bash
composer require korriganmaster/lite-collection
```

## Usage

### Basic Example

```php
use Korriganmaster\LiteCollection\LiteCollection;
use Korriganmaster\LiteCollection\Storage\SqliteStorage;

// Create in-memory storage
$storage = new SqliteStorage();
$collection = new LiteCollection($storage);

// Add items
$collection[] = ['id' => 1, 'name' => 'Item 1'];
$collection[] = ['id' => 2, 'name' => 'Item 2'];

// Access items
echo $collection[0]['name']; // "Item 1"

// Count items
echo count($collection); // 2

// Iterate over the collection
foreach ($collection as $item) {
    echo $item['name'];
}
```

### Associative Mode with Custom Key

```php
use Korriganmaster\LiteCollection\Storage\SqliteStorage;
use Korriganmaster\LiteCollection\Storage\StorageInterface;

// Create storage with a custom primary key
$storage = new SqliteStorage(
    StorageInterface::MODE_ASSOCIATIVE, 
    'custom_id'
);

$storage->insert(['custom_id' => 100, 'name' => 'Item 100']);

// Access by custom key
$item = $storage->findById(100);
```

### Persistent Disk Storage

```php
// Create disk storage
$storage = new SqliteStorage(
    StorageInterface::MODE_NORMAL,
    'id',
    'path/to/database.sqlite'
);
```

## API

### LiteCollection

The [`LiteCollection`](src/LiteCollection.php) class provides a collection interface:

- **`count(): int`**: Returns the number of items
- **`offsetGet($offset): mixed`**: Retrieves an item by its index
- **`offsetSet($offset, $value): void`**: Sets or updates an item
- **`offsetUnset($offset): void`**: Removes an item
- **`offsetExists($offset): bool`**: Checks if an item exists
- **`getIterator(): Traversable`**: Returns an iterator to traverse the collection

### SqliteStorage

The [`SqliteStorage`](src/Storage/SqliteStorage.php) class implements [`StorageInterface`](src/Storage/StorageInterface.php):

- **`insert(mixed $item): void`**: Inserts a new item
- **`findById(int $id): mixed`**: Finds an item by its ID
- **`exists(int $id): bool`**: Checks if an item exists
- **`update(int $id, mixed $item): void`**: Updates an existing item
- **`delete(int $id): void`**: Deletes an item
- **`count(): int`**: Returns the number of items
- Implements `Iterator` to traverse items

### StorageInterface

The [`StorageInterface`](src/Storage/StorageInterface.php) interface defines the contract for storage systems:

**Storage Modes:**
- `StorageInterface::MODE_NORMAL`: Normal mode with auto-incremented keys
- `StorageInterface::MODE_ASSOCIATIVE`: Associative mode with custom primary key

## Testing

The project includes unit tests using PHPUnit:

```bash
composer test
```

Tests cover:
- [`LiteCollectionTest`](test/LiteCollectionTest.php): Collection tests
- [`SqliteStorageTest`](test/SqliteStorageTest.php): SQLite storage tests

## Development

### Requirements

- PHP 8.0 or higher
- SQLite3 extension
- Composer

### Development Tools

```bash
# Run tests
vendor/bin/phpunit

# Static analysis
vendor/bin/phpstan analyse

# Code style fixing
vendor/bin/php-cs-fixer fix
```

## Architecture

The project follows a simple architecture:

- **[`LiteCollection`](src/LiteCollection.php)**: Main collection class
- **[`AbstractStorage`](src/Storage/AbstractStorage.php)**: Abstract class for storage systems
- **[`SqliteStorage`](src/Storage/SqliteStorage.php)**: SQLite storage implementation
- **[`StorageInterface`](src/Storage/StorageInterface.php)**: Interface defining the storage contract

## License

This project is licensed under the MIT License.