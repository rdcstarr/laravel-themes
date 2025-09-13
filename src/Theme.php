<?php

namespace Rdcstarr\Themes;

use Illuminate\Filesystem\Filesystem;

class Theme
{
	protected Filesystem $filesystem;
	public string $name;

	public function __construct()
	{
		$this->filesystem = new Filesystem();
	}

	public function setCurrentTheme($name): bool
	{
		if (!$this->exists($name))
		{
			abort(500, "The theme [{$name}] does not exist.");
		}

		$this->name = $name;
		return true;
	}

	public function name(): string
	{
		return $this->name;
	}

	public function basePath(): string
	{
		return resource_path('themes');
	}

	public function path(): string
	{
		return resource_path("themes/{$this->name}");
	}

	public function viewsPath(): string
	{
		return resource_path("themes/{$this->name}/views");
	}

	public function jsPath(): string
	{
		return resource_path("themes/{$this->name}/js/app.js");
	}

	public function cssPath(): string
	{
		return resource_path("themes/{$this->name}/css/app.css");
	}

	public function viteJs(): string
	{
		return "resources/themes/{$this->name}/js/app.js";
	}

	public function viteCss(): string
	{
		return "resources/themes/{$this->name}/css/app.css";
	}

	public function viteImages(): string
	{
		return "resources/themes/{$this->name}/images";
	}

	public function exists(string $name): bool
	{
		return $this->filesystem->exists(resource_path("themes/{$name}"));
	}
}
