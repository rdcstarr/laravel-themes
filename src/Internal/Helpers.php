<?php

namespace Rdcstarr\Themes\Internal;

use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class Helpers
{
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

	public static function isCacheable(): bool
	{
		return app()->environment('production') && !app()->runningInConsole() && Cache::getStore() instanceof RedisStore;
	}
}
