<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Rdcstarr\Themes\Commands\ThemeAddCommand;
use Rdcstarr\Themes\Commands\ThemeListCommand;
use Rdcstarr\Themes\Commands\ThemePublishVite;
use Rdcstarr\Themes\Commands\ThemeRemoveCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ThemesServiceProvider extends PackageServiceProvider
{
	/**
	 * Register services and bind the theme singleton in the service container.
	 */
	public function register(): void
	{
		parent::register();

		$this->app->singleton('theme', ThemeManager::class);
	}

	/**
	 * Bootstrap any application services, configure view and Blade integration, and Vite macros.
	 */
	public function boot(): void
	{
		parent::boot();

		// Set the default theme from configuration if specified
		if (config()->has('themes.default'))
		{
			theme()->set(config('themes.default', 'default'));
		}

		// Configure view paths and Blade directives
		View::prependLocation(theme()->viewsPath());
		Blade::directive('themeName', fn() => "<?php echo theme()->name(); ?>");

		// Configure Vite to use the current theme's build directory and hot file
		view()->composer('*', function ()
		{
			Vite::useBuildDirectory("themes/" . theme()->name());
			Vite::useHotFile("." . theme()->name() . ".hot");
		});

		// Define Vite macros for theme assets
		Vite::macro('image', fn(string $file) => $this->asset(theme()->viteImages() . "/{$file}"));
	}

	/**
	 * Configure the package using Laravel Package Tools.
	 *
	 * @param Package $package
	 * @return void
	 */
	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 *
		 * More info: https://github.com/spatie/laravel-package-tools
		 */
		$package->name('themes')
			->hasConfigFile()
			->hasCommands([
				ThemeAddCommand::class,
				ThemeListCommand::class,
				ThemePublishVite::class,
				ThemeRemoveCommand::class,
			]);
	}
}
