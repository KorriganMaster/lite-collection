# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-12-21

### Added
- Initial release of LiteCollection library
- `LiteCollection` class implementing `Countable`, `ArrayAccess`, and `IteratorAggregate` interfaces
- `SqliteStorage` class for data persistence using SQLite
- `StorageInterface` and `AbstractStorage` for storage abstraction
- Two operating modes:
  - `MODE_NORMAL`: Auto-incremented keys (0, 1, 2...)
  - `MODE_ASSOCIATIVE`: Custom primary key support
- In-memory SQLite storage support (default)
- Persistent disk SQLite storage support
- Data compression using gzcompress for efficient storage
- Full CRUD operations (Create, Read, Update, Delete)
- Iterator support for traversing collections
- PHPUnit test suite with coverage for core functionality
- PHPStan static analysis configuration (level max)
- PHP-CS-Fixer code style configuration
- Comprehensive README with usage examples and API documentation
- MIT License

[unreleased]: https://github.com/korriganmaster/lite-collection/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/korriganmaster/lite-collection/releases/tag/v1.0.0
