<?php

/**
 * Get the current Themes instance from the service container.
 *
 * @return \Rdcstarr\Themes\ThemeManager
 */
if (!function_exists('theme'))
{
	function theme(): \Rdcstarr\Themes\ThemeManager
	{
		return app('theme');
	}
}
