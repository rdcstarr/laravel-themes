<?php

namespace Rdcstarr\Themes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Themes\Theme
 */
class Theme extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Themes\Theme::class;
	}
}
