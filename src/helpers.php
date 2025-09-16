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

/**
 * Get the current ViteInline instance from the service container.
 *
 * @return \Rdcstarr\Themes\ViteInline
 */
if (!function_exists('viteInline'))
{
	function viteInline(): \Rdcstarr\Themes\ViteInline
	{
		return app('viteInline');
	}
}
