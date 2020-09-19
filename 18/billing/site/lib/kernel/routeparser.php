<?php
namespace Kernel;

class RouteParser
{
	private const VARIABLE_REGEX = "~\{\s*([a-zA-Z0-9_]+)\s*(?::\s*([^{]+))?\}\??~";

	private const DEFAULT_DISPATCH_REGEX = '([^/]+)';

	public function parse($route): array
	{
		preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

		if (empty($matches))
		{
			return [$route];
		}

		$search = [];
		$variables = [];
		foreach ($matches as $set)
		{
			$search[] = $set[0][0];
			$variables[$set[1][0]] = $set[1][0];
		}

		$route = str_replace($search, self::DEFAULT_DISPATCH_REGEX, $route);

		return [$route, $variables];
	}
}