<?php

namespace SevenPercent;

use SevenPercent\HTTP\Request;
use SevenPercent\HTTP\Response\ClientError\NotFound;
use SevenPercent\HTTP\Response\ClientError\MethodNotAllowed;

abstract class RegexRouter implements RouterInterface {

	const CONTROLLER_NAMESPACE = 'Controllers';

	protected static $routes = [];

	final public static function serve(Request $httpRequest) {
		foreach (static::$routes as $regex => $controllerClassName) {
			if (preg_match($regex, $httpRequest->url->path, $parameters) === 1) {
				$namespacedControllerClassName = ltrim(static::CONTROLLER_NAMESPACE . "\\$controllerClassName", '\\');
				if (method_exists($namespacedControllerClassName, $httpRequest->method)) {
					$x = 0;
					while (isset($parameters[$x])) {
						unset($parameters[$x]);
						++$x;
					}
					$response = (new $namespacedControllerClassName())->{$httpRequest->method}(...$parameters);
				} else {
					$response = new MethodNotAllowed();
				}
				break;
			}
		}
		if (!isset($response)) {
			$response = new NotFound();
		}
		return $response->send();
	}
}
