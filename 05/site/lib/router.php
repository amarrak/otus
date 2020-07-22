<?php

/**
 * Class Router
 */
class Router
{
	/**
	 * @var RouteParser
	 */
	private $routeParser;

	/**
	 * @var array
	 */
	private $staticRoutes = [];

	/**
	 * @var array
	 */
	private $variableRoutes = [];

	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';
	const HTTP_PUT = 'PUT';
	const HTTP_DELETE = 'DELETE';

	public function __construct(RouteParser $routeParser = null)
	{
		$this->routeParser = $routeParser ?: new RouteParser();
	}

	public function get($route, $handler)
	{
		return $this->addRoute(self::HTTP_GET, $route, $handler);
	}

	public function post($route, $handler)
	{
		return $this->addRoute(self::HTTP_POST, $route, $handler);
	}

	public function put($route, $handler)
	{
		return $this->addRoute(self::HTTP_PUT, $route, $handler);
	}

	public function delete($route, $handler)
	{
		return $this->addRoute(self::HTTP_DELETE, $route, $handler);
	}

	public function addRoute($httpMethod, $route, $handler)
	{
		$route = trim($route, '/');
		$routeData = $this->routeParser->parse($route);

		isset($routeData[1]) ?
			$this->addVariableRoute($httpMethod, $routeData, $handler) :
			$this->addStaticRoute($httpMethod, $routeData, $handler);

		return $this;
	}

	private function addStaticRoute($httpMethod, $routeData, $handler)
	{
		$template = $routeData[0];
		$this->staticRoutes[$template][$httpMethod] = array($handler, []);
	}

	private function addVariableRoute($httpMethod, $routeData, $handler)
	{
		list($regex, $variables) = $routeData;
		$regex = '~^'.$regex . '$~';
		$this->variableRoutes[$regex][$httpMethod] = [$handler, $variables];
	}

	public function dispatch($httpMethod, $uri)
	{
		list($handler, $vars) = $this->dispatchRoute($httpMethod, trim($uri, '/'));

		return call_user_func_array($handler, $vars);
	}

	private function dispatchRoute($httpMethod, $uri)
	{
		if (isset($this->staticRoutes[$uri][$httpMethod]))
		{
			return $this->staticRoutes[$uri][$httpMethod];
		}

		return $this->dispatchVariableRoute($httpMethod, $uri);
	}

	private function dispatchVariableRoute($httpMethod, $uri)
	{
		foreach ($this->variableRoutes as $regex => $route)
		{
			if (!preg_match($regex, $uri, $matches))
			{
				continue;
			}

			if (!isset($route[$httpMethod]))
			{
				continue;
			}

			foreach (array_values($route[$httpMethod][1]) as $i => $varName)
			{
				if (!isset($matches[$i + 1]) || $matches[$i + 1] === '')
				{
					unset($route[$httpMethod][1][$varName]);
				}
				else
				{
					$route[$httpMethod][1][$varName] = $matches[$i + 1];
				}
			}

			return $route[$httpMethod];
		}

		throw new RuntimeException("Route is not found");
	}
}
