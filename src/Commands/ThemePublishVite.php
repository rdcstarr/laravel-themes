<?php

namespace Rdcstarr\Themes\Commands;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
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
			$this->publishStub(
				from: "$stubsPath/vite.config.js.stub",
				to: base_path(),
				name: 'vite.config.js'
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

	/**
	 * Publishes a stub file to the specified location, replacing placeholders with provided values.
	 *
	 * This method reads the contents of a stub file, replaces all placeholders in the format {{ key }}
	 * with their corresponding values from the $replacements array, and writes the result to the target path.
	 * If an error occurs during reading or writing, it logs the error and returns false.
	 *
	 * @param string $from         Path to the source stub file.
	 * @param string $to           Directory where the file should be published.
	 * @param string $name         Name of the file to create.
	 * @param array  $replacements Key-value pairs for placeholder replacement.
	 * @return bool                True on success, false on failure.
	 */
	protected function publishStub(string $from, string $to, string $name, array $replacements = []): bool
	{
		$path = "$to/$name";

		try
		{
			$content = File::get($from);

			collect($replacements)->each(function ($replace, $search) use (&$content)
			{
				$content = Str::replace("{{ $search }}", $replace, $content);
			});

			File::put($path, $content);

			return true;
		}
		catch (Exception $e)
		{
			$this->components->error("Failed to publish stub '{$path}': " . $e->getMessage());
			return false;

		}
	}
}
