<?php

namespace SampleChat\Core;

use Psr\Http\Message\ServerRequestInterface;

class Router
{

    /* @var Route[] */
    private $routes = [];
    /* @var Route */
    private $defaultRoute;

    public function addRoute(Route $route)
    {
        array_push($this->routes, $route);
    }

    public function addDefaultRoute(Route $route)
    {
        $this->defaultRoute = $route;
    }

    public function run(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $result = $this->getRouteForRequest($request)->startHandling($request);

        return $result->withProtocolVersion($request->getProtocolVersion());
    }

    private function getRouteForRequest(ServerRequestInterface $request)
    {
        foreach ($this->routes as $route) {
            if ($route->isHit($request)) {
                return $route;
            }
        }
        return $this->defaultRoute;
    }
}
