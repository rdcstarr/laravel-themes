# Laravel Themes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)

A simple Laravel package for theme management.

## Features

- Add, list, and remove themes from your application
- Automatic integration with Vite for theme assets (CSS, JS, images)
- Support for custom directories and files per theme
- Global `theme()` helper for accessing the current theme
- Artisan commands for theme management
- Prepend location for theme views
- Blade directive for displaying the theme name

## Installation

You can install the package via composer:

```bash
composer require rdcstarr/laravel-themes
```

## Usage

### Artisan Commands

- **Add a new theme:**
  ```bash
  php artisan theme:add {theme-name}
  ```
  Creates the directory structure and files for a new theme.

- **List existing themes:**
  ```bash
  php artisan theme:list
  ```
  Shows all available themes.

- **Remove a theme:**
  ```bash
  php artisan theme:remove {theme-name} [--force]
  ```
  Deletes the specified theme. The `--force` option deletes without confirmation.

### Helper and API

- **Get the current theme instance:**
  ```php
  theme()
  ```
- **Set the current theme:**
  ```php
  theme()->set('theme-name');
  ```
- **Get the current theme name:**
  ```php
  theme()->name();
  ```
- **Get paths for theme assets:**
  ```php
  theme()->basePath(); // all themes
  theme()->path(); // current theme
  theme()->viewsPath(); // views in current theme
  theme()->jsPath(); // theme JS
  theme()->cssPath(); // theme CSS
  theme()->viteJs(); // Vite JS entry
  theme()->viteCss(); // Vite CSS entry
  theme()->viteImages(); // images directory
  theme()->getAll(); // all available themes
  theme()->exists('theme-name'); // check if theme exists
  ```

### Blade Integration

- **Display the theme name in Blade:**
  ```blade
  @themeName
  ```

- **Include Vite assets for the current theme:**
  ```blade
  @vite(theme()->viteCss())
  @vite(theme()->viteJs())
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
