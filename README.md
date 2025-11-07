# Laravel TOON

Laravel integration for TOON - A human-readable data serialization format.

This package provides Laravel-specific features on top of the [robertgdev/php-toon](https://github.com/robertgdev/php-toon) core library.

## Installation

```bash
composer require robertgdev/laravel-toon
```

This will automatically install the `robertgdev/php-toon` core library as a dependency.

## Configuration

### Publishing Configuration

Publish the configuration file to customize default encoding/decoding options:

```bash
php artisan vendor:publish --tag=toon-config
```

This creates `config/toon.php` where you can set default options.

### Configuration Options

The config file supports both file-based configuration and environment variables:

```php
// config/toon.php
return [
    'encode' => [
        'indent' => env('TOON_ENCODE_INDENT', 2),
        'delimiter' => env('TOON_ENCODE_DELIMITER', ','),
        'lengthMarker' => env('TOON_ENCODE_LENGTH_MARKER', false),
    ],
    'decode' => [
        'indent' => env('TOON_DECODE_INDENT', 2),
        'strict' => env('TOON_DECODE_STRICT', true),
        'objectsAsStdClass' => env('TOON_DECODE_OBJECTS_AS_STDCLASS', false),
    ],
];
```

### Environment Variables

Add these to your `.env` file to configure TOON globally:

```env
# Encoding options
TOON_ENCODE_INDENT=2
TOON_ENCODE_DELIMITER=,
TOON_ENCODE_LENGTH_MARKER=false

# Decoding options
TOON_DECODE_INDENT=2
TOON_DECODE_STRICT=true
TOON_DECODE_OBJECTS_AS_STDCLASS=false
```

**Available Delimiters:**
- `,` (comma, default)
- `\t` (tab - use `"\t"` in config or `\t` in .env)
- `|` (pipe)

**Length Marker:**
- `false` (default) - no marker
- `true` or `#` - adds `#` prefix to array lengths

**Objects as StdClass:**
- `false` (default) - objects decode to associative arrays
- `true` - objects decode to `StdClass` instances (enables perfect round-trips)

### Using Configured Defaults

When you use the facade without options, it automatically uses your configured defaults:

```php
use RobertGDev\LaravelToon\Facades\Toon;

// Uses config defaults
$encoded = Toon::encode($data);
$decoded = Toon::decode($encoded);

// Override with custom options
use RobertGDev\Toon\Types\EncodeOptions;
$encoded = Toon::encode($data, new EncodeOptions(indent: 4));
```

## Programmatic Usage

### Using the Facade (Laravel-style)

The package provides a Laravel facade for easy access:

```php
use RobertGDev\LaravelToon\Facades\Toon;
use RobertGDev\Toon\Types\EncodeOptions;

// Simple encoding
$data = ['name' => 'Ada', 'age' => 30, 'active' => true];
$encoded = Toon::encode($data);

// With options
$options = new EncodeOptions(
    indent: 4,
    delimiter: "\t",
    lengthMarker: '#'
);
$encoded = Toon::encode($data, $options);

// Decoding
$decoded = Toon::decode($encoded);
```

The facade is automatically registered via package discovery as `Toon`, so you can also use it without importing:

```php
$encoded = \Toon::encode(['key' => 'value']);
$decoded = \Toon::decode($encoded);
```

### Using the Core Library Directly

You can also use the core library directly:

```php
use RobertGDev\Toon\Toon;

$encoded = Toon::encode(['name' => 'Ada']);
$decoded = Toon::decode($encoded);
```

For detailed API documentation, see the [robertgdev/php-toon](https://github.com/robertgdev/php-toon) package.

## Artisan Command

The package includes an Artisan command for converting between JSON and TOON formats:

```bash
# Encode JSON to TOON
php artisan toon:convert input.json --output=output.toon

# Decode TOON to JSON
php artisan toon:convert input.toon --output=output.json

# Auto-detect mode based on file extension
php artisan toon:convert data.json  # Encodes to TOON
php artisan toon:convert data.toon  # Decodes to JSON

# Print to stdout instead of file
php artisan toon:convert input.json

# Use custom delimiter (tab or pipe)
php artisan toon:convert input.json --delimiter="\t"
php artisan toon:convert input.json --delimiter="|"

# Use length marker
php artisan toon:convert input.json --length-marker

# Show token statistics
php artisan toon:convert input.json --stats

# Custom indentation
php artisan toon:convert input.json --indent=4

# Disable strict mode for decoding
php artisan toon:convert input.toon --no-strict
```

### Command Options

- `input` - Input file path (required)
- `--o|output` - Output file path (prints to stdout if not specified)
- `--e|encode` - Force encode mode (auto-detected by default)
- `--d|decode` - Force decode mode (auto-detected by default)
- `--delimiter` - Delimiter for arrays: comma (,), tab (\t), or pipe (|)
- `--indent` - Indentation size (default: 2)
- `--length-marker` - Use length marker (#) for arrays
- `--strict` - Enable strict mode for decoding (default: true)
- `--no-strict` - Disable strict mode for decoding
- `--stats` - Show token statistics

## Features

- **Laravel Facade**: Use `Toon::encode()` and `Toon::decode()` anywhere in your Laravel app
- **Artisan Command**: Convert files between JSON and TOON formats via CLI
- **Auto-Registration**: Service provider and facade automatically registered via package discovery
- **Service Container**: Toon class registered as a singleton in Laravel's container
- **File Operations**: Read and write TOON files with ease
- **Token Statistics**: Estimate token savings when converting to TOON

## Package Structure

This package is a thin Laravel integration layer. The core TOON functionality is provided by the `robertgdev/php-toon` package, which is a standalone PHP library.

### What's in this package:
- [`ToonServiceProvider`](src/ToonServiceProvider.php) - Registers the service and command
- [`Toon` Facade](src/Facades/Toon.php) - Laravel facade for easy access
- [`ToonCommand`](src/Console/ToonCommand.php) - Artisan command for file conversion
- Configuration file with Laravel integration
- Comprehensive integration test suite (38 tests covering all features)

### What's in the core package:
- All encoding/decoding logic
- Type definitions and options
- Core TOON parser and serializer

See [robertgdev/php-toon](https://github.com/robertgdev/php-toon) for the core library documentation.

## Requirements

- PHP 8.2+
- Laravel 10.x or 11.x or 12.x
- robertgdev/php-toon (automatically installed)

## Testing

Run the test suite with:

```bash
vendor/bin/pest
```

The package includes 38 comprehensive tests covering:
- Artisan command functionality (15 tests)
- Configuration and service provider features (10 tests)
- Laravel integration and facade functionality (13 tests)

## License

MIT License