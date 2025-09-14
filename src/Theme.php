<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class Theme
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
	public function basePath(): string
	{
		return resource_path('themes');
	}

	/**
	 * Get the path for the current theme.
	 *
	 * @return string
	 */
	public function path(): string
	{
		return resource_path("themes/{$this->name}");
	}

	/**
	 * Get the views path for the current theme.
	 *
	 * @return string
	 */
	public function viewsPath(): string
	{
		return resource_path("themes/{$this->name}/views");
	}

	/**
	 * Get the JavaScript file path for the current theme.
	 *
	 * @return string
	 */
	public function jsPath(): string
	{
		return resource_path("themes/{$this->name}/js/app.js");
	}

	/**
	 * Get the CSS file path for the current theme.
	 *
	 * @return string
	 */
	public function cssPath(): string
	{
		return resource_path("themes/{$this->name}/css/app.css");
	}

	/**
	 * Get the Vite JavaScript entry path for the current theme.
	 *
	 * @return string
	 */
	public function viteJs(): string
	{
		return "resources/themes/{$this->name}/js/app.js";
	}

	/**
	 * Get the Vite CSS entry path for the current theme.
	 *
	 * @return string
	 */
	public function viteCss(): string
	{
		return "resources/themes/{$this->name}/css/app.css";
	}

	/**
	 * Get the Vite images directory path for the current theme.
	 *
	 * @return string
	 */
	public function viteImages(): string
	{
		return "resources/themes/{$this->name}/images";
	}

	/**
	 * Check if a theme exists by name.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists(string $name): bool
	{
		$path = resource_path("themes/{$name}");

		// Use Redis cache only if Redis is the cache driver
		if (app()->environment('production') && !app()->runningInConsole() && Cache::getStore() instanceof RedisStore)
		{
			static $memo = [];

			if (isset($memo[$name]))
			{
				return $memo[$name];
			}

			$cacheKey = "theme:exists:{$name}";

			return $memo[$name] ??= Cache::remember($cacheKey, 30, fn() => File::isDirectory($path));
		}

		// Fallback to direct file check
		return File::isDirectory($path);
	}

	/**
	 * Get all available theme names.
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		return collect(File::directories($this->basePath()))
			->map(fn($path) => basename($path))
			->values()
			->toArray();
	}
}
