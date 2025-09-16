<?php

namespace Rdcstarr\Themes\Commands;

use Artisan;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;

class ThemeInstallCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:install
		{--manifest : Set this to create a manifest.json file}
		{--force : If set, will overwrite existing files without prompting}
	';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install theme package';

	/**
	 * The name of the default theme.
	 *
	 * @var string
	 */
	protected $themeName = 'default';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$force = $this->option('force');

		if (!$force)
		{
			if (!confirm("If you continue, this will overwrite any existing files. Do you want to proceed?"))
			{
				$this->info('Theme installation was canceled.');
				return self::SUCCESS;
			}
		}

		if (theme()->exists($this->themeName))
		{
			if (!confirm("A default theme already exists. Do you want to continue and overwrite existing files?"))
			{
				$this->info('Theme installation was canceled.');
				return self::SUCCESS;
			}
		}

		$this->components->info('Starting Themes Package Installation...');

		$steps = [
			'🎨 Create default theme' => 'runCreateDefaultTheme',
			'⚡ Publish vite config'   => 'runPublishViteConfig',
		];

		collect($steps)->each(function ($method, $name)
		{
			try
			{
				$this->components->task($name, fn() => $this->{$method}());
			}
			catch (Exception $e)
			{
				$this->components->error($name . ' failed: ' . $e->getMessage());
				exit;
			}
		});

		$this->components->success('Themes Package Installation Completed Successfully!');
	}

	/**
	 * Run the command to create the default theme.
	 */
	protected function runCreateDefaultTheme()
	{
		$themePath = theme()->basePath() . '/' . $this->themeName;
		File::deleteDirectory($themePath);

		Artisan::call('theme:add', [
			'name'       => $this->themeName,
			'--manifest' => $this->option('manifest'),
		]);
	}

	/**
	 * Run the command to publish the Vite config file.
	 */
	protected function runPublishViteConfig()
	{
		Artisan::call('theme:publish-vite', ['--force' => true]);
	}
}
