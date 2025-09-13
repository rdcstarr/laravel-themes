<?php

/**
 * Get the current Themes instance from the service container.
 *
 * @return \Rdcstarr\Themes\Theme
 */
if (!function_exists('theme'))
{
	function theme(): \Rdcstarr\Themes\Theme
	{
		return app('theme');
	}
}
