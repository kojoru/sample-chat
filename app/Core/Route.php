<?php

namespace SampleChat\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SampleChat\Dtos\AbstractRequest;

class Route implements RequestHandlerInterface
{
    /* @var string */
    private $path;

    /* @var callable */
    private $action;

    /* @var string */
    private $method;

    /* @var AbstractRequest */
    private $requestTemplate;

    /* @var RequestMapper */
    private $requestMapper;

    /* @var MiddlewareInterface[] */
    private $middlewares;

    function __construct(RequestMapper $requestMapper, string $path, callable $action)
    {
        $this->requestMapper = $requestMapper;
        $this->path = $path;
        $this->action = $action;

        $this->method = "GET";
        $this->requestTemplate = null;
        $this->middlewares = [];
    }

    function withMethod(string $method)
    {
        if ($this->method === $method) {
            return $this;
        }

        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    function withRequestTemplate(AbstractRequest $requestTemplate)
    {
        if ($this->requestTemplate === $requestTemplate) {
            return $this;
        }

        $new = clone $this;
        $new->requestTemplate = $requestTemplate;
        return $new;
    }

    function withMiddlewares(array $middlewares)
    {
        if ($this->middlewares === $middlewares) {
            return $this;
        }

        $new = clone $this;
        $new->middlewares = $middlewares;
        return $new;
    }

    function isHit(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === $this->method
            && $request->getUri()->getPath() === $this->path;
    }

    function startHandling(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middlewares);

        return $this->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \JsonMapper_Exception
     */
    function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middlewares) {
            $middleware = current($this->middlewares);
            if ($middleware) {
                next($this->middlewares);
                return $middleware->process($request, $this);
            }
        }
        $dto = $this->requestMapper->requestToDto($request, $this->requestTemplate);
        if ($dto) {
            $dto->validate();

            if (!$dto->isValid()) {
                $response = ['errors' => $dto->getValidationProblems()];
                return $this->requestMapper->dtoToResponse($response, 400);
            }
        }
        $callResult = call_user_func($this->action, $dto, $request);
        if ($callResult instanceof ResponseInterface) {
            return $callResult;
        } else {
            return $this->requestMapper->dtoToResponse($callResult);
        }
    }
}
