<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Theme
	|--------------------------------------------------------------------------
	|
	| This option controls the default theme that will be used by the application.
	| You may set this to any of the themes defined in the "themes" array below.
	|
	*/

	'default' => env('THEME_DEFAULT', 'default'),

	/*
	|--------------------------------------------------------------------------
	| Theme Directories
	|--------------------------------------------------------------------------
	|
	| This option defines the directories for theme resources and built assets.
	| You may change these paths to suit your application's structure.
	|
	|*/

	'directories' => [

		/*
		|--------------------------------------------------------------------------
		| Themes Resource Directory
		|--------------------------------------------------------------------------
		|
		| This is the directory where your themes are stored. This path is
		| relative to the resources directory of your Laravel application.
		|
		*/

		'resources' => 'themes',

		/*
		|--------------------------------------------------------------------------
		| Themes Build Directory
		|--------------------------------------------------------------------------
		|
		| This is the directory where your themes' built assets are stored.
		| This path is relative to the public directory of your Laravel application.
		|
		*/

		'build' => 'themes',
	],

];
