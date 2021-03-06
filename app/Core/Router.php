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

        return $result
            ->withProtocolVersion($request->getProtocolVersion())
            ->withAddedHeader('Content-Security-Policy', 'default-src \'self\'; object-src \'none\'')
            ->withAddedHeader('X-Content-Type-Options', 'nosniff')
            ->withAddedHeader('X-Frame-Options', 'DENY')
            ->withAddedHeader('X-XSS-Protection', '1; mode=block');
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
