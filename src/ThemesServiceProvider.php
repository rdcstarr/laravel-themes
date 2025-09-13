<?php

namespace Rdcstarr\Themes;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rdcstarr\Themes\Commands\ThemesCommand;

class ThemesServiceProvider extends PackageServiceProvider
{
	public function register(): void
	{
		parent::register();

		$this->app->singleton('theme', fn($app) => new Theme());
	}

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
