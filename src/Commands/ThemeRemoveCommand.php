<?php

namespace Rdcstarr\Themes\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;

class ThemeRemoveCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'theme:remove
		{name : The name of the theme}
		{--force : Force removal without confirmation}
	';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove a theme from the application';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$name      = $this->argument('name');
		$force     = $this->option('force');
		$themePath = theme()->basePath() . '/' . $name;

		if (!theme()->exists($name))
		{
			$this->components->error(
				"Theme '{$name}' does not exist."
			);

			return self::FAILURE;
		}

		if (!$force)
		{
			if (!confirm("Are you sure you want to delete the theme '{$name}'?"))
			{
				$this->info('Theme deletion was canceled.');
				return self::SUCCESS;
			}
		}

		File::deleteDirectory($themePath);

		$this->components->success("Theme '{$name}' has been removed successfully!");

		return self::SUCCESS;
	}
}
