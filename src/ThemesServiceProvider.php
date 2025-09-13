<?php

namespace Rdcstarr\Themes;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Themes\Commands\ThemesCommand;

class ThemesServiceProvider extends PackageServiceProvider
{
	/**
	 * Register services and bind the theme singleton in the service container.
	 */
	public function register(): void
	{
		parent::register();

		$this->app->singleton('theme', fn($app) => new Theme());
	}

	/**
	 * Bootstrap any application services, configure view and Blade integration, and Vite macros.
	 */
	public function boot(): void
	{
		parent::boot();

		View::prependLocation(theme()->viewsPath());
		Blade::directive('themeName', fn() => "<?php echo theme()->name(); ?>");

		view()->composer('*', function ()
		{
			Vite::useBuildDirectory("themes/" . theme()->name());
			Vite::useHotFile("." . theme()->name() . ".hot");
		});

		Vite::macro('image', fn(string $file) => $this->asset("resources/themes/" . theme()->name() . "/images/{$file}"));
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
			->hasCommand(ThemesCommand::class);
	}
}
