<?php

namespace Rdcstarr\Themes\Commands;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rdcstarr\Themes\Internal\Helpers;
use function Laravel\Prompts\confirm;

class ThemeManifestPublishCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:manifest-publish
		{theme : The name of the theme}
		{--force : Force creation/recreation without confirmation}
	';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create or recreate a manifest.json file for an existing theme';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$themeName = $this->argument('theme');
		$force     = $this->option('force');

		if (!theme()->exists($themeName))
		{
			$this->components->error("Theme '{$themeName}' does not exist.");
			$this->line("You can create the theme with: <fg=cyan>php artisan theme:add {$themeName} --manifest</>");
			return self::FAILURE;
		}

		$themePath    = theme()->basePath() . "/{$themeName}";
		$manifestPath = "{$themePath}/manifest.json";
		$stubsPath    = __DIR__ . '/../../stubs';

		// Check if manifest already exists
		if (File::exists($manifestPath))
		{
			if (!$force)
			{
				$this->components->warn("Theme '{$themeName}' already has a manifest.json file.");
				$this->showExistingManifest($manifestPath);

				if (!confirm("Do you want to recreate the manifest file? This will overwrite the existing one."))
				{
					$this->info('Manifest creation was canceled.');
					return self::SUCCESS;
				}
			}

			$this->components->info("Recreating manifest.json for theme '{$themeName}'...");
		}
		else
		{
			$this->components->info("Creating manifest.json for theme '{$themeName}'...");
		}

		try
		{
			$currentDate = now()->format('Y-m-d H:i');

			// Generate manifest.json from stub
			$success = Helpers::publishStub(
				from: "{$stubsPath}/manifest.json.stub",
				to: $themePath,
				name: 'manifest.json',
				replacements: [
					'name'       => Str::ucfirst($themeName),
					'created_at' => $currentDate,
				]
			);

			if (!$success)
			{
				throw new Exception("Failed to create manifest.json file.");
			}

			$this->components->success("Manifest file has been created successfully!");
			$this->line("  Path: [./" . Str::replaceFirst(base_path() . '/', '', $manifestPath) . "]");
			$this->newLine();

			// Show the created manifest
			$this->showManifestInfo($manifestPath);

			// Show usage examples
			$this->line("  <fg=gray>Next steps:</>");
			$this->line("  <fg=gray>• Add fields:</> <fg=cyan>php artisan theme:manifest {$themeName} add</>");
			$this->line("  <fg=gray>• List fields:</> <fg=cyan>php artisan theme:manifest {$themeName} list</>");
			$this->line("  <fg=gray>• Remove fields:</> <fg=cyan>php artisan theme:manifest {$themeName} remove</>");
			$this->newLine();

			return self::SUCCESS;
		}
		catch (Exception $e)
		{
			$this->components->error("Failed to create manifest for theme '{$themeName}': " . $e->getMessage());
			return self::FAILURE;
		}
	}

	/**
	 * Show information about existing manifest.
	 */
	protected function showExistingManifest(string $manifestPath): void
	{
		try
		{
			$content  = File::get($manifestPath);
			$manifest = json_decode($content, true);

			if (json_last_error() === JSON_ERROR_NONE && isset($manifest['fields']))
			{
				$fieldCount = count($manifest['fields']);
				$this->line("  <fg=yellow>Current manifest has {$fieldCount} field(s):</>");

				if ($fieldCount > 0)
				{
					collect($manifest['fields'])->take(3)->each(function ($field, $index)
					{
						$this->line("  <fg=gray>• {$field['label']} ({$field['key']})</>");
					});

					if ($fieldCount > 3)
					{
						$this->line("  <fg=gray>• ... and " . ($fieldCount - 3) . " more</>");
					}
				}
			}
		}
		catch (Exception $e)
		{
			$this->line("  <fg=red>Existing manifest appears to be corrupted.</>");
		}
	}

	/**
	 * Show information about the created manifest.
	 */
	protected function showManifestInfo(string $manifestPath): void
	{
		try
		{
			$content  = File::get($manifestPath);
			$manifest = json_decode($content, true);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw new Exception('Invalid JSON in manifest file.');
			}

			$this->line("  <fg=gray>Manifest details:</>");
			$this->line("  <fg=yellow>Theme:</> <fg=white>{$manifest['name']}</>");
			$this->line("  <fg=yellow>Created:</> <fg=white>{$manifest['created_at']}</>");
			$this->line("  <fg=yellow>Fields:</> <fg=white>" . count($manifest['fields'] ?? []) . "</>");
		}
		catch (Exception $e)
		{
			$this->line("  <fg=red>Manifest appears to be corrupted or unreadable.</>");
		}
	}
}
