<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\RedisStore;

class ThemeManager
{
	public string $name = 'default';

	/**
	 * Set the current theme by name.
	 * Throws an exception if the theme does not exist.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function set($name): bool
	{
		if (!$this->exists($name))
		{
			abort(500, "The theme [{$name}] does not exist.");
		}

		$this->name = $name;
		return true;
	}

	/**
	 * Get the name of the current theme.
	 *
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Get the base path for all themes.
	 *
	 * @return string
	 */
	public function basePath(bool $relative = false): string
	{
		$path = resource_path('themes');

		return $relative ? $this->relativePath($path) : $path;
	}

	/**
	 * Get the path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function path(?string $themeName = null, bool $relative = false): string
	{
		$themeName ??= $this->name;
		$path      = resource_path("themes/{$themeName}");

		return $relative ? $this->relativePath($path) : $path;
	}

	/**
	 * Get the views path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function viewsPath(?string $themeName = null, bool $relative = false): string
	{
		$themeName ??= $this->name;
		$path      = resource_path("themes/{$themeName}/views");

		return $relative ? $this->relativePath($path) : $path;
	}

	/**
	 * Get the JavaScript file path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function jsPath(?string $themeName = null, bool $relative = false): string
	{
		$themeName ??= $this->name;
		$path      = resource_path("themes/{$themeName}/js/app.js");

		return $relative ? $this->relativePath($path) : $path;
	}

	/**
	 * Get the CSS file path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function cssPath(?string $themeName = null, bool $relative = false): string
	{
		$themeName ??= $this->name;
		$path      = resource_path("themes/{$themeName}/css/app.css");

		return $relative ? $this->relativePath($path) : $path;
	}

	/**
	 * Get the Vite JavaScript entry path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function viteJs(?string $themeName = null): string
	{
		$themeName ??= $this->name;

		return "themes/{$themeName}/js/app.js";
	}

	/**
	 * Get the Vite CSS entry path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function viteCss(?string $themeName = null): string
	{
		$themeName ??= $this->name;

		return "themes/{$themeName}/css/app.css";
	}

	/**
	 * Get the Vite images directory path for a specific theme or the current theme if no name is provided.
	 *
	 * @param string|null $themeName The name of the theme (optional, defaults to current theme)
	 * @return string
	 */
	public function viteImages(?string $themeName = null): string
	{
		$themeName ??= $this->name;

		return "themes/{$themeName}/images";
	}

	/**
	 * Check if a theme exists by name.
	 * Uses Redis caching in production for performance when Redis is available.
	 *
	 * @param string $name The name of the theme to check
	 * @return bool
	 */
	public function exists(string $name): bool
	{

		// Use Redis cache only if Redis is the cache driver
		if (app()->environment('production') && !app()->runningInConsole() && Cache::getStore() instanceof RedisStore)
		{
			static $memo = [];

			if (isset($memo[$name]))
			{
				return $memo[$name];
			}

			$cacheKey = "theme:exists:{$name}";

			return $memo[$name] ??= Cache::remember($cacheKey, 30, fn() => File::isDirectory($this->path($name)));
		}

		// Fallback to direct file check
		return File::isDirectory($this->path($name));
	}

	/**
	 * Get all available theme names from the themes directory.
	 *
	 * @return array Array of theme names
	 */
	public function getAll(): array
	{
		return collect(File::directories($this->basePath()))
			->map(fn($path) => basename($path))
			->values()
			->toArray();
	}

	protected function relativePath(string $path): string
	{
		return ltrim(str_replace(base_path(), '', $path), '/');
	}
}
