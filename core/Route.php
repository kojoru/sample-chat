<?php

namespace SampleChat\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
    /* @var string */
    private $path;

    /* @var callable */
    private $action;

    /* @var string */
    private $method;

    /* @var */
    private $requestTemplate;

    function __construct(string $path, callable $action, string $method = "GET", object $requestTemplate = null)
    {
        $this->path = $path;
        $this->action = $action;
        $this->method = $method;
        $this->requestTemplate = $requestTemplate;
    }

    function isHit(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === $this->method
            && $request->getUri()->getPath() === $this->path;
    }

    function process(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func($this->action, $this->requestTemplate, $request->getQueryParams());
    }
}
