<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Rdcstarr\Themes\Commands\ThemeAddCommand;
use Rdcstarr\Themes\Commands\ThemeInstallCommand;
use Rdcstarr\Themes\Commands\ThemeListCommand;
use Rdcstarr\Themes\Commands\ThemeManifestCommand;
use Rdcstarr\Themes\Commands\ThemeManifestPublishCommand;
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
		$this->app->singleton('viteInline', ViteInline::class);
	}

	/**
	 * Bootstrap any application services, configure view and Blade integration, and Vite macros.
	 */
	public function boot(): void
	{
		parent::boot();

		$this->setDefaultTheme();
		$this->configureViews();
		$this->configureBladeDirectives();
		$this->configureVite();
	}

	/**
	 * Set the default theme from configuration if specified.
	 */
	protected function setDefaultTheme(): void
	{
		if (config()->has('themes.default'))
		{
			theme()->set(config('themes.default', 'default'));
		}
	}

	/**
	 * Configure view paths for the current theme.
	 */
	protected function configureViews(): void
	{
		View::prependLocation(theme()->viewsPath());
	}

	/**
	 * Configure Blade directives for themes.
	 */
	protected function configureBladeDirectives(): void
	{
		Blade::directive('themeName', fn() => "<?php echo theme()->name(); ?>");
		Blade::directive('viteInline', fn($expression) => "<?php echo viteInline()->render({$expression}); ?>");
		Blade::directive('viteCssInline', fn() => "<?php echo viteInline()->render([theme()->viteCss()]); ?>");
		Blade::directive('viteJsInline', fn() => "<?php echo viteInline()->render([theme()->viteJs()]); ?>");
		Blade::directive('viteCss', fn() => "<?php echo app('Illuminate\Foundation\Vite')(theme()->viteCss()); ?>");
		Blade::directive('viteJs', fn() => "<?php echo app('Illuminate\Foundation\Vite')(theme()->viteJs()); ?>");
	}

	/**
	 * Configure Vite for theme support.
	 */
	protected function configureVite(): void
	{
		// Configure Vite to use the current theme's build directory and hot file
		view()->composer('*', function ()
		{
			Vite::useBuildDirectory(theme()->getBuildDirectoryPath());
			Vite::useHotFile(theme()->getHotFile());
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
				ThemeInstallCommand::class,
				ThemeListCommand::class,
				ThemeManifestCommand::class,
				ThemeManifestPublishCommand::class,
				ThemePublishVite::class,
				ThemeRemoveCommand::class,
			]);
	}
}
