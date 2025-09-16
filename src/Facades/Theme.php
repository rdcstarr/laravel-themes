<?php

namespace Rdcstarr\Themes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Themes\ThemeManager
 */
class Theme extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Themes\ThemeManager::class;
	}
}
