<?php

namespace Rdcstarr\Themes\Internal;

use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class Helpers
{
	/**
	 * Publish a stub file to a given location with optional replacements.
	 *
	 * @param string $from The path to the stub file.
	 * @param string $to The directory where the file should be published.
	 * @param string $name The name of the file to be created.
	 * @param array $replacements Key-value pairs for placeholder replacements in the stub.
	 * @return bool True on success, false on failure.
	 */
	public static function publishStub(string $from, string $to, string $name, array $replacements = []): bool
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
			return false;
		}
	}

	/**
	 * Determine if caching should be used based on the environment and cache store.
	 *
	 * @return bool True if caching is enabled, false otherwise.
	 */
	public static function isCacheable(): bool
	{
		return app()->environment('production') && !app()->runningInConsole() && Cache::getStore() instanceof RedisStore;
	}
}
