<?php

namespace Rdcstarr\Settings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;
use Rdcstarr\Settings\Settings;

class SettingsCommand extends Command
{
	protected $signature = 'settings
        {action : Action (get, set, list, clear, refresh)}
        {key? : Setting key}
        {value? : Setting value}
        {--group= : Setting group}';

	protected $description = 'Manage application settings';

	public function handle(): int
	{
		$action = $this->argument('action');

		return match ($action)
		{
			'get' => $this->handleGet(),
			'set' => $this->handleSet(),
			'list' => $this->handleList(),
			'clear' => $this->handleClear(),
			'refresh' => $this->handleRefresh(),
			default => $this->handleInvalid()
		};
	}

	private function handleGet(): int
	{
		$key = $this->argument('key');

		if (!$key)
		{
			$this->error('Key is required for get action');
			return self::FAILURE;
		}

		$group    = $this->option('group');
		$settings = app(Settings::class);
		$value    = $settings->get($key, null, $group);

		if ($value === null)
		{
			$this->warn("Setting '{$key}' not found");
			return self::FAILURE;
		}

		$this->info("Key: {$key}");
		$this->info("Group: " . ($group ?? 'default'));
		$this->info("Value: " . json_encode($value));

		return self::SUCCESS;
	}

	private function handleSet(): int
	{
		$key   = $this->argument('key');
		$value = $this->argument('value');

		if (!$key || $value === null)
		{
			$this->error('Key and value are required for set action');

			return self::FAILURE;
		}

		$group    = $this->option('group');
		$settings = app(Settings::class);

		$success = $settings->set($key, $value, $group);

		if ($success)
		{
			$this->info("Setting '{$key}' set successfully");

			return self::SUCCESS;
		}

		$this->error("Failed to set setting '{$key}'");

		return self::FAILURE;
	}

	private function handleList(): int
	{
		$group    = $this->option('group');
		$settings = app(Settings::class);

		if ($group)
		{
			$data = $settings->group($group);
			$this->info("Settings in group '{$group}':");
		}
		else
		{
			$data = $settings->all();
			$this->info('All settings:');
		}

		if ($data->isEmpty())
		{
			$this->warn('No settings found');

			return self::SUCCESS;
		}

		$this->table(['Key', 'Value', 'Type'], $data->map(function ($value, $key)
		{
			return [
				$key,
				is_string($value) ? $value : json_encode($value),
				gettype($value),
			];
		})->values());

		return self::SUCCESS;
	}

	private function handleClear(): int
	{
		$group = $this->option('group');

		if ($group)
		{
			$count = Setting::where('group', $group)->count();
			Setting::where('group', $group)->delete();
			$this->info("Deleted {$count} settings from group '{$group}'");
		}
		else
		{
			if (!$this->confirm('Are you sure you want to delete ALL settings?'))
			{
				$this->info('Operation cancelled');
				return self::SUCCESS;
			}

			$count = Setting::count();
			Setting::truncate();
			$this->info("Deleted all {$count} settings");
		}

		// Refresh cache
		app(Settings::class)->refresh();
		$this->info('Cache refreshed');

		return self::SUCCESS;
	}

	private function handleRefresh(): int
	{
		$cacheKey   = config('settings.cache_key', 'app_settings_cache');
		$cacheStore = config('settings.cache_store', 'redis');

		Cache::store($cacheStore)->forget($cacheKey);
		app(Settings::class)->refresh();

		$this->info('Settings cache refreshed successfully');

		// Arată statistici
		$totalSettings = Setting::count();
		$totalGroups   = Setting::distinct('group')->count();

		$this->info("Total settings: {$totalSettings}");
		$this->info("Total groups: {$totalGroups}");

		return self::SUCCESS;
	}

	private function handleInvalid(): int
	{
		$this->error('Invalid action. Available actions: get, set, list, clear, refresh');
		return self::FAILURE;
	}
}
