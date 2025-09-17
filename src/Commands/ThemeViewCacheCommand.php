<?php

namespace Rdcstarr\Themes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ThemeViewCacheCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'view:cache
        {--clear : Clear the view cache before caching}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Compile all Blade templates (standard Laravel views + all theme views)";

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if ($this->option('clear'))
		{
			$this->callSilent('view:clear');
		}

		$compiler = $this->laravel['view']->getEngineResolver()->resolve('blade')->getCompiler();

		// Cache standard Laravel views
		$standardViewsPath = resource_path('views');
		if (File::isDirectory($standardViewsPath))
		{
			$this->compileViewsInPath($standardViewsPath, $compiler);
		}

		// Cache all theme views
		foreach (theme()->getAll() as $themeName)
		{
			$viewsPath = theme()->basePath() . "/{$themeName}/views";
			if (File::isDirectory($viewsPath))
			{
				$this->compileViewsInPath($viewsPath, $compiler);
			}
		}

		$this->components->info('Blade templates cached successfully.');
	}

	/**
	 * Compile all Blade views in a given path.
	 */
	protected function compileViewsInPath(string $path, $compiler): void
	{
		$extensions = collect($this->laravel['view']->getExtensions())
			->filter(fn($value) => $value === 'blade')
			->keys()
			->map(fn($extension) => "*.{$extension}")
			->all();

		collect(Finder::create()->in($path)->exclude('vendor')->name($extensions)->files())
			->each(fn($file) => $compiler->compile($file->getRealPath()));
	}
}
