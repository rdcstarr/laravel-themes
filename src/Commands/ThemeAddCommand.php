<?php

namespace Rdcstarr\Themes\Commands;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ThemeAddCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:add {name : The name of the theme}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add a new theme to the application';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$name      = $this->argument('name');
		$themePath = theme()->basePath() . '/' . $name;
		$stubsPath = __DIR__ . '/../../stubs';

		if (theme()->exists($name))
		{
			$this->components->error(
				"Theme '{$name}' already exists.\n" .
				"Check the themes directory: [" . $themePath . "]"
			);

			return self::FAILURE;
		}

		$currentDate = now()->format('Y-m-d H:i');

		try
		{
			$directories = [
				$themePath,
				"$themePath/css",
				"$themePath/images",
				"$themePath/js",
				"$themePath/views",
			];

			// Create all directories
			collect($directories)->each(fn($dir) => File::ensureDirectoryExists($dir));

			// Generate app.css from stub
			$this->publishStub(
				from: "$stubsPath/app.css.stub",
				to: "$themePath/css",
				name: 'app.css',
				replacements: [
					'name'       => Str::ucfirst($name),
					'created_at' => $currentDate,
				]
			);

			// Generate app.js from stub
			$this->publishStub(
				from: "$stubsPath/app.js.stub",
				to: "$themePath/js",
				name: 'app.js',
				replacements: [
					'name'       => Str::ucfirst($name),
					'created_at' => $currentDate,
				]
			);

			// Generate wellcome.blade.php from stub
			$this->publishStub(
				from: "$stubsPath/wellcome.blade.php.stub",
				to: "$themePath/views",
				name: 'welcome.blade.php',
				replacements: [
					'name' => Str::ucfirst($name),
				]
			);

			$this->components->success("Theme '{$name}' has been created successfully!");
			$this->line("  Path: [" . Str::replaceFirst(base_path() . '/', '', $themePath) . "]");

			return self::SUCCESS;

		}
		catch (Exception $e)
		{
			$this->components->error("Failed to create theme '{$name}': " . $e->getMessage());

			// Șterge directorul dacă a fost creat parțial
			if (File::exists($themePath))
			{
				File::deleteDirectory($themePath);
			}

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
