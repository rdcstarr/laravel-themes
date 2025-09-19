<?php

namespace Rdcstarr\Themes;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use Rdcstarr\Themes\Internal\Helpers;
use RuntimeException;

class ViteInline
{
	protected Vite $vite;

	public function __construct()
	{
		$this->vite = new Vite();
		$this->configureVite();
	}

	/**
	 * Configure Vite with theme-specific settings.
	 *
	 * @return void
	 */
	protected function configureVite(): void
	{
		$this->vite->useBuildDirectory(theme()->getBuildDirectoryPath());
		$this->vite->useHotFile(theme()->getHotFile());
	}

	/**
	 * Render inline assets from Vite manifest or hot server when running hot.
	 *
	 * @param mixed $entrypoints
	 * @return HtmlString
	 */
	public function render($entrypoints): HtmlString
	{
		if ($this->vite->isRunningHot())
		{
			return $this->vite->__invoke($entrypoints);
		}

		return $this->renderFromManifest(new Collection($entrypoints));
	}

	/**
	 * Render assets from the Vite manifest file.
	 *
	 * @param Collection $entrypoints
	 * @return HtmlString
	 */
	protected function renderFromManifest(Collection $entrypoints): HtmlString
	{
		$manifest = $this->getManifest();

		foreach ($entrypoints as $entrypoint)
		{
			$chunk    = $this->getChunkFromManifest($manifest, $entrypoint);
			$filePath = $this->getAssetPath($chunk['file']);

			$this->validateAssetExists($filePath);

			return $this->renderAssetContent($filePath);
		}

		throw new RuntimeException("No valid entrypoints provided for rendering.");
	}

	/**
	 * Get and validate the Vite manifest.
	 *
	 * @return array
	 */
	protected function getManifest(): array
	{
		$manifestPath = $this->getManifestPath();

		if (!is_file($manifestPath))
		{
			throw new RuntimeException("Vite manifest not found at: {$manifestPath}");
		}

		// Use cache only when the application cache is considered cacheable
		if (Helpers::isCacheable())
		{
			static $memo = [];

			$themeKey = theme()->name();

			if (isset($memo[$themeKey]))
			{
				return $memo[$themeKey];
			}

			$cacheKey = "theme:manifest:{$themeKey}";

			return $memo[$themeKey] ??= Cache::remember($cacheKey, 30, fn() => File::json($manifestPath));
		}

		// Fallback to direct file read
		return File::json($manifestPath);
	}

	/**
	 * Get chunk data from manifest for a specific entrypoint.
	 *
	 * @param array $manifest
	 * @param string $entrypoint
	 * @return array
	 * @throws RuntimeException
	 */
	protected function getChunkFromManifest(array $manifest, string $entrypoint): array
	{
		if (!Arr::has($manifest, $entrypoint))
		{
			throw new RuntimeException("Entrypoint [{$entrypoint}] not found in Vite manifest.");
		}

		return Arr::get($manifest, $entrypoint);
	}

	/**
	 * Get the full path to an asset file in the public build directory.
	 *
	 * @param string $file
	 * @return string
	 */
	protected function getAssetPath(string $file): string
	{
		return public_path(theme()->getBuildDirectoryPath() . '/' . $file);
	}

	/**
	 * Validate that an asset file exists on disk.
	 *
	 * @param string $path
	 * @return void
	 * @throws RuntimeException
	 */
	protected function validateAssetExists(string $path): void
	{
		if (!File::isFile($path))
		{
			throw new RuntimeException("Unable to locate file from Vite manifest: {$path}.");
		}
	}

	/**
	 * Render the content of an asset file as an HTML string.
	 * Supports JavaScript and CSS files only.
	 *
	 * @param string $path
	 * @return HtmlString
	 * @throws RuntimeException
	 */
	protected function renderAssetContent(string $path): HtmlString
	{
		$fileType = pathinfo($path, PATHINFO_EXTENSION);

		// Use cache only when the application cache is considered cacheable
		if (Helpers::isCacheable())
		{
			static $memo = [];

			$cacheKey = "theme:asset:content:" . md5($path);

			$content = $memo[$cacheKey] ?? $memo[$cacheKey] ??= Cache::remember($cacheKey, 30, fn() => File::get($path));
		}
		else
		{
			// Fallback to direct file read
			$content = File::get($path);
		}

		return match ($fileType)
		{
			'js' => new HtmlString("<script>{$this->minifyJs($content)}</script>"),
			'css' => new HtmlString("<style>{$this->minifyCss($content)}</style>"),
			default => throw new RuntimeException("Unsupported file type [{$fileType}] for Vite asset rendering."),
		};
	}

	/**
	 * Minify JavaScript content by removing comments and unnecessary whitespace.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function minifyJs(string $content): string
	{
		if (trim($content) === "")
		{
			return $content;
		}

		return preg_replace(
			[
				// Remove comment(s)
				'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
				// Remove white-space(s) outside the string and regex
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
				// Remove the last semicolon
				'#;+\}#',
				// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
				'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
				// --ibid. From `foo['bar']` to `foo.bar`
				'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i',
			],
			[
				'$1',
				'$1$2',
				'}',
				'$1$3',
				'$1.$3',
			],
			$content);
	}

	/**
	 * Minify CSS content by removing comments and unnecessary whitespace.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function minifyCss(string $content): string
	{
		if (trim($content) === "")
		{
			return $content;
		}

		return preg_replace(
			[
				// Remove comment(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				// Minify string value
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s)
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
			],
			[
				'$1',
				'$1$2$3$4$5$6$7',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2',
			],
			$content);
	}

	/**
	 * Get the path to the manifest file in the public build directory.
	 *
	 * @return string
	 */
	protected function getManifestPath(): string
	{
		return public_path(theme()->getBuildDirectoryPath() . "/manifest.json");
	}
}
