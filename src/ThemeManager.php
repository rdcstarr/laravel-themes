<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Themes\Internal\Helpers;

class ThemeManager
{
	/**
	 * The current theme name. Defaults to 'default'.
	 *
	 * @var string
	 */
	public string $name = 'default';

	/**
	 * Returns the hot file name for the current theme.
	 *
	 * @return string
	 */
	public function getHotFile(): string
	{
		return ".{$this->name}.hot";
	}

	/**
	 * Returns the resources directory name from configuration.
	 *
	 * @return string
	 */
	protected function resourcesDirectory(): string
	{
		return config("themes.directories.resources", "themes");
	}

	/**
	 * Returns the build directory name from configuration.
	 *
	 * @return string
	 */
	protected function buildDirectory(): string
	{
		return config("themes.directories.build", "themes");
	}

	/**
	 * Returns the relative build directory path for the current theme.
	 *
	 * @return string
	 */
	public function getBuildDirectoryPath(): string
	{
		return $this->buildDirectory() . "/{$this->name}";
	}

	/**
	 * Set the current theme by name.
	 * Throws an exception if the theme does not exist (unless running in console).
	 *
	 * @param string $name
	 * @return bool
	 * @throws \RuntimeException
	 */
	public function set(string $name): bool
	{
		if (!$this->exists($name) && !app()->runningInConsole())
		{
			throw new \RuntimeException("The theme [{$name}] does not exist.");
		}

		$this->name = $name;
		return true;
	}

	/**
	 * Get the current theme name.
	 *
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Get the base path for all themes (resources directory).
	 *
	 * @return string
	 */
	public function basePath(): string
	{
		return resource_path($this->resourcesDirectory());
	}

	/**
	 * Get the path for the current theme.
	 *
	 * @return string
	 */
	public function path(): string
	{
		return $this->basePath() . "/{$this->name}";
	}

	/**
	 * Get the views path for the current theme.
	 *
	 * @return string
	 */
	public function viewsPath(): string
	{
		return $this->path() . "/views";
	}

	/**
	 * Get the main JS file path for the current theme.
	 *
	 * @return string
	 */
	public function jsPath(): string
	{
		return $this->path() . "/js/app.js";
	}

	/**
	 * Get the main CSS file path for the current theme.
	 *
	 * @return string
	 */
	public function cssPath(): string
	{
		return $this->path() . "/css/app.css";
	}

	/**
	 * Get the Vite entry path for JS (relative to project root).
	 *
	 * @return string
	 */
	public function viteJs(): string
	{
		return "resources/{$this->resourcesDirectory()}/{$this->name}/js/app.js";
	}

	/**
	 * Get the Vite entry path for CSS (relative to project root).
	 *
	 * @return string
	 */
	public function viteCss(): string
	{
		return "resources/{$this->resourcesDirectory()}/{$this->name}/css/app.css";
	}

	/**
	 * Get the Vite images directory path for the current theme (relative to project root).
	 *
	 * @return string
	 */
	public function viteImages(): string
	{
		return "resources/{$this->resourcesDirectory()}/{$this->name}/images";
	}

	/**
	 * Check if a theme exists by name.
	 * Uses cache when available (via Helpers::isCacheable()).
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists(string $name): bool
	{
		$path = $this->basePath() . "/{$name}";

		// Use cache only when the application cache is considered cacheable
		if (Helpers::isCacheable())
		{
			static $memo = [];

			if (isset($memo[$name]))
			{
				return $memo[$name];
			}

			$cacheKey = "theme:exists:{$name}";

			return $memo[$name] ??= Cache::remember($cacheKey, 30, fn() => File::isDirectory($path));
		}

		// Fallback to direct filesystem check
		return File::isDirectory($path);
	}

	/**
	 * Return all available theme names.
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

	/**
	 * Get the manifest for a theme as an array.
	 * If the file does not exist, returns null.
	 *
	 * @param string|null $name
	 * @return array|null
	 */
	public function getManifest(?string $name = null): ?array
	{
		$themeName    = $name ?? $this->name;
		$manifestPath = $this->basePath() . "/{$themeName}/manifest.json";

		return File::json($manifestPath);
	}
}
