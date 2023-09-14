<?php

namespace MVC\Filesystem;

class PathResolver
{

	public static function resolve(string $path, array $stringArguments): string
	{
		$content = explode('%', $path);
		$result = array_map(function ($segment) use ($stringArguments) {
			if (in_array($segment, $stringArguments)) return $stringArguments[$segment];
			return $segment;
		}, $content);
		return implode('', $result);
	}
}