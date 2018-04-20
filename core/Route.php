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

    /**
     * @param ServerRequestInterface $request
     * @param RequestMapper $mapper
     * @return ResponseInterface
     * @throws \JsonMapper_Exception
     */
    function process(ServerRequestInterface $request, RequestMapper $mapper): ResponseInterface
    {
        $dto = $mapper->requestToDto($request, $this->requestTemplate);
        $callResult = call_user_func($this->action, $dto, $request->getQueryParams(), $request);
        if ($callResult instanceof ResponseInterface) {
            return $callResult;
        } else {
            return $mapper->dtoToResponse($callResult);
        }
    }
}
