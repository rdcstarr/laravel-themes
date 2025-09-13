<?php

namespace Rdcstarr\Themes\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;

class ThemeListCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:list';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List all available themes';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$themes = collect(theme()->getAll());

		if ($themes->isEmpty())
		{
			$this->components->info("No themes found.");
		}
		else
		{
			$this->components->info('Found ' . $themes->count() . ' themes:');

			$themes->each(function ($theme)
			{
				$this->line("  🎨 " . Str::ucfirst($theme));
				$this->line("  Path: [" . Str::replaceFirst(base_path() . '/', '', theme()->basePath() . "/$theme") . "]");
				$this->line("  <fg=gray>" . str_repeat("·", 100) . "</>");
			});
		}

		return self::SUCCESS;
	}
}
