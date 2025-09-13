<?php

if (!function_exists('theme'))
{
	function theme(): \Rdcstarr\Themes\Theme
	{
		return app('theme');
	}
}
