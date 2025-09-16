<?php

namespace Rdcstarr\Themes\Commands;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rdcstarr\Themes\Internal\Helpers;
use function Laravel\Prompts\confirm;

class ThemePublishVite extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:publish-vite {--force : Overwrite existing Vite config files if they exist}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish Vite config files for the current theme';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$force     = $this->option('force');
		$stubsPath = __DIR__ . '/../../stubs';

		if (!$force)
		{
			if (!confirm("If you continue, this will overwrite any existing Vite config file. Do you want to proceed?"))
			{
				$this->info('Vite config file publishing was canceled.');
				return self::SUCCESS;
			}
		}

		try
		{
			// Publish vite.config.js from stub
			Helpers::publishStub(
				from: "$stubsPath/vite.config.js.stub",
				to: base_path(),
				name: 'vite.config.js',
				replacements: [
					'resources_relative_path' => "resources/" . config("themes.directories.resources", "themes"),
					'build_relative_path'     => config("themes.directories.build", "themes"),
					'hot_file'                => '.${theme}.hot',
				]
			);

			$this->components->success("Vite config file published successfully!");
			$this->line("  Path: [./" . Str::replaceFirst(base_path() . '/', '', base_path('vite.config.js')) . "]");

			return self::SUCCESS;
		}
		catch (Exception $e)
		{
			$this->components->error("Failed to publish vite.config.js: " . $e->getMessage());
			return self::FAILURE;
		}
	}
}
