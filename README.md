# 🎨 Laravel Themes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-themes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-themes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-themes.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-themes)

> A powerful and flexible Laravel package for theme management with seamless Vite integration.

## ✨ Features

- 🎨 **Easy Theme Management** - Add, list, and remove themes with simple Artisan commands 🛠️
- ⚡ **Vite Integration** - Full Vite support with hot reload and optimized builds ⚡️🔥
- 🔄 **Inline Asset Rendering** - Render CSS/JS assets inline for performance optimization 🚀
- 📁 **Flexible Directory Structure** - Customize theme directories via configuration 🗂️
- 🎯 **Global Helper Functions** - Access themes anywhere with the `theme()` helper 🧩
- 🧩 **Blade Directives** - Convenient Blade directives for theme integration ✨
- 🚀 **Redis Caching** - Optimized performance with Redis caching support ⚡️🧠
- 📦 **Auto-Discovery** - Automatic service provider registration ✅

## 📦 Installation

Install the package via Composer:

```bash
composer require rdcstarr/laravel-themes
```

###  ⚙️ Required Setup

> 💡 **Important:** The Vite configuration is regenerated depending on which directories you change in `config/themes.php`.
>
> - If you only change `themes.directories.build` (the public/build path), you can update the Vite configuration without reinstalling themes by running `php artisan theme:publish-vite`
>
>   This updates Vite entries and build paths only.
>
> - If you change `themes.directories.resources` (the resources/themes path) — for example where theme views, CSS/JS and images live — you must run `php artisan theme:install`
>
>   `theme:install` validates and (re)creates theme resources and the Vite setup and may overwrite generated files.

1. **Install the theme package** (required first step):
   ```bash
   php artisan theme:install
   ```

2. **Publish the configuration file** (optional):
   ```bash
   php artisan vendor:publish --tag=theme-config
   ```

## 🛠️ Artisan Commands

🚀 **Install Theme Package** (required first):
```bash
php artisan theme:install [--force]
```
Sets up the default theme and publishes Vite configuration files.

🎨 **Add a New Theme**:
```bash
php artisan theme:add {theme-name} [--manifest]
```
Creates a complete theme structure with CSS, JS, views, and images directories. Use `--manifest` to also create a manifest.json file.

📋 **List Available Themes**:
```bash
php artisan theme:list
```
Shows all available themes with their paths.

🗑️ **Remove a Theme**:
```bash
php artisan theme:remove {theme-name} [--force]
```
Deletes the specified theme. Use `--force` to skip confirmation.

📝 **Create/Recreate Theme Manifest**:
```bash
php artisan theme:manifest-publish {theme-name} [--force]
```
Creates or recreates a manifest.json file for an existing theme. Use `--force` to skip confirmation when overwriting.

📑 **Manage Theme Manifest Fields**:
```bash
# List all fields in a theme's manifest
php artisan theme:manifest {theme-name} list

# Add a new field interactively
php artisan theme:manifest {theme-name} add

# Add a field with specific parameters
php artisan theme:manifest {theme-name} add --key=about.title --label="About Title" --type=text

# Remove a field
php artisan theme:manifest {theme-name} remove [--key=field-key]
```
Manage custom fields in theme manifest files for dynamic content configuration.

⚡ **Publish Vite Configuration**:
```bash
php artisan theme:publish-vite [--force]
```
Updates the Vite configuration file with theme support.

## 🎯 Theme API

**Basic Operations**:
```php
// Get the current theme instance 🧭
theme()

// Set the current theme 🎚️
theme()->set('theme-name');

// Get the current theme name 🏷️
theme()->name();

// Check if a theme exists ✅
theme()->exists('theme-name');

// Get all available themes 📚
theme()->getAll();
```

**Path Helpers**:
```php
theme()->basePath();  // Base path for all themes
theme()->path();      // Current theme path
theme()->viewsPath(); // Views directory
theme()->jsPath();    // JavaScript file path
theme()->cssPath();   // CSS file path
```

**Vite Integration**:
```php
theme()->viteJs();                // Vite JS entry point
theme()->viteCss();               // Vite CSS entry point
theme()->viteImages();            // Vite images directory
theme()->getBuildDirectoryPath(); // Build output directory
theme()->getHotFile();            // Hot reload file
```

## 🎨 Blade Directives

**Theme Information**:
```blade
{{-- Display current theme name --}}
@themeName
```

**Asset Integration**:
```blade
{{-- Standard Vite assets (requires Vite dev server or build) --}}
@vite(theme()->viteCss())
@vite(theme()->viteJs())

{{-- Standard Vite assets (requires Vite dev server or build) --}}
@viteCss // with auto path to theme()->viteCss()
@viteJs  // with auto path to theme()->viteJs()

{{-- Inline assets (injects CSS/JS directly into HTML) --}}
@viteCssInline
@viteJsInline

{{-- Custom inline assets --}}
@viteInline([theme()->viteCss(), theme()->viteJs()])
```

**Images with Vite**:
```blade
{{-- In your Blade templates --}}
<img src="{{ Vite::image('logo.png') }}" alt="Logo">
```

### ⚙️ Configuration

The `config/themes.php` file allows you to customize directories:

```php
return [
    'default' => env('THEME_DEFAULT', 'default'),

    'directories' => [
        'resources' => 'themes', // complete path will be `./resources/themes`
        'build'     => 'themes', // complete path will be `./public/themes`
    ],
];
```

## 🔥 Vite Development

🏁 **Start development server for a specific theme**:
```bash
npm run dev --theme=my-theme
```

🏢 **Build assets for production**:
```bash
npm run build --theme=my-theme
```

**Vite Configuration Features**:
- ✅ Theme-specific hot reload files
- ✅ Automatic theme directory validation
- ✅ Asset path resolution
- ✅ Full page reload on view changes

## 📁 Theme Structure

Each theme follows this organized structure:

```
resources/themes/my-theme/
├── css/
│   └── app.css          # Main CSS file
├── js/
│   └── app.js           # Main JavaScript file
├── images/              # Theme images
└── views/               # Blade templates
    └── welcome.blade.php
```

## 🚆 Middleware Integration:
```php
// Set theme based on your preferences
public function handle($request, Closure $next)
{
    theme()->set('theme_name' ?? 'default');

    return $next($request);
}
```

## ⚡ Performance Features

**Redis Caching**:
- ✅ Automatic caching when Redis is configured 🧠
- ✅ Theme existence checks cached for 30 seconds ⏱️
- ✅ Manifest files cached for 30 seconds 📦
- ✅ Asset content cached for 30 seconds 💾

**Inline Asset Benefits**:
- ✅ Reduces HTTP requests 🌐
- ✅ Eliminates render-blocking resources 🚫🧱
- ✅ Perfect for critical CSS/JS 🚀
- ✅ Automatic caching in production ✅

## 🧪 Testing

```bash
composer test
```

## 📖 Resources
 - [Changelog](CHANGELOG.md) for more information on what has changed recently. ✍️

## 👥 Credits
 - [Rdcstarr](https://github.com/rdcstarr) 🙌

## 📜 License
 - [License](LICENSE.md) for more information. ⚖️
