<?php

namespace Rdcstarr\Themes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Settings\Models\Setting;
use Rdcstarr\Settings\Settings;

class ThemesCommand extends Command
{
	protected $signature = 'themes';

	protected $description = 'Manage application themes';

	public function handle()
	{

	}
}
