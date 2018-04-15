<?php

namespace SampleChat\Core;

use Psr\Http\Message\ServerRequestInterface;

class Router
{

    /* @var Route[] */
    private $routes = [];

    function addRoute(Route $route)
    {
        array_push($this->routes, $route);
    }

    function run(ServerRequestInterface $request)
    {
        foreach ($this->routes as $route) {
            if ($route->isHit($request)) {
                return $route->process($request);
            }
        }
        return null;
    }
}
