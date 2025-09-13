# Sample laravel settings package.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)

A simple and elegant Laravel package for managing application settings with groups, caching, and multiple access methods.

## Features

- 🔧 **Group-based organization** - Organize settings into logical groups
- ⚡ **Automatic caching** - Built-in cache management for optimal performance
- 🎯 **Multiple access methods** - Use helper function, facade, or dependency injection
- 📦 **Batch operations** - Set multiple settings efficiently
- 🔄 **Method chaining** - Fluent interface for better developer experience

## Installation

You can install the package via composer:

```bash
composer require rdcstarr/laravel-themes
```

## Usage

### Quick Start

```php
// Set a single setting
settings(['app_name' => 'My Laravel App']);

// Get a setting with default
$theme = settings('ui_theme', 'light');

// Work with groups
settings()->group('mail')->set('driver', 'smtp');
$mailDriver = settings()->group('mail')->get('driver');
```

### 1. Setting Values

#### Single Values
```php
// Using helper function (recommended)
settings(['language' => 'english']);

// Using the class directly
settings()->set('language', 'english');

// In specific groups
settings()->group('mail')->set('driver', 'smtp');
settings()->group('app')->set('timezone', 'Europe/Bucharest');
```

#### Batch Operations
```php
// Multiple settings at once (recommended for performance)
settings([
    'app_name' => 'My Application',
    'timezone' => 'Europe/Bucharest',
    'debug_mode' => false,
]);

// Group-specific batch operations
settings()->group('mail')->set([
    'driver' => 'smtp',
    'host' => 'smtp.example.com',
    'port' => 587,
    'encryption' => 'tls',
]);
```

### 2. Getting Values

#### Basic Retrieval
```php
// Get with default value
$language = settings('language', 'english');
$theme = settings('ui_theme', 'light');

// From specific groups
$mailDriver = settings()->group('mail')->get('driver', 'mail');
$cacheStore = settings()->group('cache')->get('default', 'file');
```

#### Get All Settings
```php
// All settings from default group
$allSettings = settings()->all();

// All settings from specific group
$mailSettings = settings()->group('mail')->all();
$appSettings = settings()->group('app')->all();
```

### 3. Using Facade

```php
use Rdcstarr\Settings\Facades\Settings;

// All the same methods are available
Settings::set('app_name', 'My App');
Settings::get('app_name', 'Default App');

// With groups
Settings::group('mail')->set('driver', 'smtp');
Settings::group('mail')->get('driver');

// Batch operations
Settings::set([
    'key1' => 'value1',
    'key2' => 'value2',
]);
```

### 4. Advanced Operations

#### Check if Setting Exists
```php
if (settings()->has('app_name')) {
    // Setting exists
}

if (settings()->group('mail')->has('driver')) {
    // Mail driver setting exists
}
```

#### Remove Settings
```php
// Remove from default group
settings()->forget('old_setting');

// Remove from specific group
settings()->group('mail')->forget('old_host');
```

#### Cache Management
```php
// Manually flush cache (usually not needed)
settings()->flushCache();
```

### 5. Practical Examples

#### Application Configuration
```php
// Set initial app configuration
settings([
    'app_name' => 'My Laravel App',
    'app_version' => '1.0.0',
    'maintenance_mode' => false,
]);

// Use in views
<h1>{{ settings('app_name') }}</h1>
```

#### User Preferences
```php
// Store user-specific settings
$userId = auth()->id();
settings()->group("user_{$userId}")->set([
    'theme' => 'dark',
    'language' => 'english',
    'notifications' => true,
]);

// Get user preference
$userTheme = settings()->group("user_{$userId}")->get('theme', 'light');
```

#### Feature Flags
```php
// Enable/disable features
settings()->group('features')->set([
    'new_dashboard' => true,
    'beta_features' => false,
    'api_v2' => true,
]);

// Check in code
if (settings()->group('features')->get('new_dashboard', false)) {
    // Show new dashboard
}
```

#### System Configuration
```php
// Mail configuration
settings()->group('mail')->set([
    'driver' => 'smtp',
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
]);

// Cache configuration
settings()->group('cache')->set([
    'default' => 'redis',
    'ttl' => 3600,
]);
```

### 6. Method Chaining

The package supports fluent method chaining for better readability:

```php
$mailSettings = settings()
    ->group('mail')
    ->set('driver', 'smtp')
    ->flushCache()
    ->all();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rdcstarr](https://github.com/rdcstarr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
