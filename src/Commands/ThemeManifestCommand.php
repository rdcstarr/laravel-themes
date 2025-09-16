<?php

namespace Rdcstarr\Themes\Commands;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ThemeManifestCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:manifest
		{theme : The name of the theme}
		{action : The action to perform (add|remove|list)}
		{--key= : The field key (for add/remove actions)}
		{--label= : The field label (for add action)}
		{--type= : The field type (for add action)}
	';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Manage manifest fields for a theme (add, remove, or list fields)';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$themeName = $this->argument('theme');
		$action    = $this->argument('action');

		if (!theme()->exists($themeName))
		{
			$this->components->error("Theme '{$themeName}' does not exist.");
			return self::FAILURE;
		}

		$manifestPath = theme()->basePath() . "/{$themeName}/manifest.json";

		if (!File::exists($manifestPath))
		{
			$this->components->error("Theme '{$themeName}' does not have a manifest.json file.");
			$this->line("You can create the manifest with: <fg=cyan>php artisan theme:manifest-publish {$themeName}</>");
			$this->line("Or recreate the theme with: <fg=cyan>php artisan theme:add {$themeName} --manifest</>");
			return self::FAILURE;
		}

		try
		{
			$manifest = $this->loadManifest($manifestPath);

			return match ($action)
			{
				'add' => $this->addField($manifest, $manifestPath),
				'remove' => $this->removeField($manifest, $manifestPath),
				'list' => $this->listFields($manifest, $themeName),
				default => $this->showInvalidAction($action),
			};
		}
		catch (Exception $e)
		{
			$this->components->error("Failed to process manifest: " . $e->getMessage());
			return self::FAILURE;
		}
	}

	/**
	 * Load and decode the manifest file.
	 */
	protected function loadManifest(string $path): array
	{
		$content  = File::get($path);
		$manifest = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new Exception("Invalid JSON in manifest file: " . json_last_error_msg());
		}

		// Initialize fields array if it doesn't exist
		if (!isset($manifest['fields']))
		{
			$manifest['fields'] = [];
		}

		return $manifest;
	}

	/**
	 * Save the manifest file.
	 */
	protected function saveManifest(array $manifest, string $path): void
	{
		$json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		File::put($path, $json);
	}

	/**
	 * Add a new field to the manifest.
	 */
	protected function addField(array $manifest, string $manifestPath): int
	{
		$key = $this->option('key') ?: text(
			label: 'Enter the field key (e.g., "about.title", "contact.email"):',
			required: true,
			validate: fn($value) => $this->validateFieldKey($value, $manifest)
		);

		// Check if field already exists
		$existingField = collect($manifest['fields'])->firstWhere('key', $key);
		if ($existingField)
		{
			$this->components->error("Field with key '{$key}' already exists.");
			$this->line("Existing field details:");
			$this->showFieldInfo($existingField);

			if (!confirm("Do you want to update this existing field?"))
			{
				$this->info('Field addition was canceled.');
				return self::SUCCESS;
			}

			// Remove existing field before adding the new one
			$manifest['fields'] = collect($manifest['fields'])
				->reject(fn($field) => $field['key'] === $key)
				->values()
				->toArray();
		}

		$label = $this->option('label') ?: text(
			label: 'Enter the field label:',
			required: true
		);

		$type = $this->option('type') ?: select(
			label: 'Select the field type:',
			options: [
				'text'     => 'Text',
				'textarea' => 'Textarea',
				'email'    => 'Email',
				'url'      => 'URL',
				'number'   => 'Number',
				'boolean'  => 'Boolean',
				'select'   => 'Select',
				'color'    => 'Color',
				'date'     => 'Date',
				'image'    => 'Image',
			],
			default: 'text'
		);

		$newField = [
			'key'   => $key,
			'label' => $label,
			'type'  => $type,
		];

		// Add additional properties based on field type
		if ($type === 'select')
		{
			$options = text(
				label: 'Enter select options (comma-separated):',
				placeholder: 'option1,option2,option3'
			);

			if ($options)
			{
				$newField['options'] = array_map('trim', explode(',', $options));
			}
		}

		if ($type === 'textarea')
		{
			$rows = text(
				label: 'Number of rows (optional):',
				default: '3'
			);

			if ($rows && is_numeric($rows))
			{
				$newField['rows'] = (int) $rows;
			}
		}



		$manifest['fields'][]   = $newField;
		$manifest['updated_at'] = now()->format('Y-m-d H:i');

		$this->saveManifest($manifest, $manifestPath);

		$this->components->success("Field '{$key}' has been added to the manifest.");
		$this->newLine();
		$this->line("  <fg=gray>Field details:</>");
		$this->showFieldInfo($newField);

		return self::SUCCESS;
	}

	/**
	 * Remove a field from the manifest.
	 */
	protected function removeField(array $manifest, string $manifestPath): int
	{
		if (empty($manifest['fields']))
		{
			$this->components->info("No fields found in the manifest.");
			return self::SUCCESS;
		}

		$key = $this->option('key');

		if (!$key)
		{
			$options = collect($manifest['fields'])->pluck('label', 'key')->toArray();
			$key     = select(
				label: 'Select the field to remove:',
				options: $options
			);
		}

		$fieldIndex = collect($manifest['fields'])->search(fn($field) => $field['key'] === $key);

		if ($fieldIndex === false)
		{
			$this->components->error("Field with key '{$key}' not found.");
			return self::FAILURE;
		}

		$field = $manifest['fields'][$fieldIndex];

		if (!confirm("Are you sure you want to remove the field '{$field['label']}' ({$key})?"))
		{
			$this->info('Field removal was canceled.');
			return self::SUCCESS;
		}

		unset($manifest['fields'][$fieldIndex]);
		$manifest['fields']     = array_values($manifest['fields']); // Re-index array
		$manifest['updated_at'] = now()->format('Y-m-d H:i');

		$this->saveManifest($manifest, $manifestPath);

		$this->components->success("Field '{$key}' has been removed from the manifest.");

		return self::SUCCESS;
	}

	/**
	 * List all fields in the manifest.
	 */
	protected function listFields(array $manifest, string $themeName): int
	{
		if (empty($manifest['fields']))
		{
			$this->components->info("No fields found in the manifest.");
			$this->line("You can add fields using: <fg=cyan>php artisan theme:manifest {$themeName} add</>");
			$this->newLine();

			return self::SUCCESS;
		}

		$this->components->info('Theme Manifest Information:');
		$this->newLine();

		// Show manifest metadata
		$this->line("  <fg=yellow>Theme:</> <fg=white>{$manifest['name']}</>");
		$this->line("  <fg=yellow>Created:</> <fg=white>{$manifest['created_at']}</>");
		$this->line("  <fg=yellow>Updated:</> <fg=white>{$manifest['updated_at']}</>");
		$this->newLine();

		$this->components->info('Fields (' . count($manifest['fields']) . ' total):');

		collect($manifest['fields'])->each(function ($field, $index) use ($manifest)
		{
			$this->showFieldInfo($field, $index + 1);

			if ($index < count($manifest['fields']) - 1)
			{
				$this->line("  <fg=gray>" . str_repeat("·", 80) . "</>");
			}
		});

		// Show usage examples
		$this->newLine();
		$this->line("  <fg=gray>Usage examples:</>");
		$this->line("  <fg=gray>Add field:</> <fg=cyan>php artisan theme:manifest {$themeName} add</>");
		$this->line("  <fg=gray>Remove field:</> <fg=cyan>php artisan theme:manifest {$themeName} remove</>");
		$this->line("  <fg=gray>Quick add:</> <fg=cyan>php artisan theme:manifest {$themeName} add --key=about.title --label=\"About Title\" --type=text</>");
		$this->line("  <fg=gray>Create manifest:</> <fg=cyan>php artisan theme:manifest-publish {$themeName}</>");
		$this->newLine();

		return self::SUCCESS;
	}

	/**
	 * Display field information.
	 */
	protected function showFieldInfo(array $field, ?int $number = null): void
	{
		$prefix = $number ? "  {$number}. " : "  ";

		$this->line("{$prefix}<fg=cyan>✦ {$field['label']}</>");
		$this->line("     <fg=yellow>Key:</> <fg=white>{$field['key']}</>");
		$this->line("     <fg=yellow>Type:</> <fg=green>{$field['type']}</>");

		if (isset($field['options']) && is_array($field['options']))
		{
			$this->line("     <fg=yellow>Options:</> <fg=blue>" . implode(', ', $field['options']) . "</>");
		}

		if (isset($field['rows']))
		{
			$this->line("     <fg=yellow>Rows:</> <fg=blue>{$field['rows']}</>");
		}
	}

	/**
	 * Validate the field key.
	 */
	protected function validateFieldKey(string $key, array $manifest): ?string
	{
		if (empty($key))
		{
			return 'Field key is required.';
		}

		if (!preg_match('/^[a-zA-Z0-9_.]+$/', $key))
		{
			return 'Field key can only contain letters, numbers, dots, and underscores.';
		}

		// Note: We handle duplicates in addField method with user confirmation
		return null;
	}

	/**
	 * Show invalid action error.
	 */
	protected function showInvalidAction(string $action): int
	{
		$this->components->error("Invalid action '{$action}'. Available actions: add, remove, list");
		return self::FAILURE;
	}
}
