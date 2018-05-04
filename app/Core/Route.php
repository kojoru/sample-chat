<?php

namespace SampleChat\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SampleChat\Dtos\AbstractRequest;
use SampleChat\Exceptions\AccessDeniedException;

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

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \JsonMapper_Exception
     */
    function startHandling(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middlewares);

        try {
            return $this->handle($request);
        } catch (AccessDeniedException $e) {
            $response = ['errors' => ['Access denied: ' . $e->getMessage()]];
            return $this->requestMapper->dtoToResponse($response, 403);
        } catch (\InvalidArgumentException $e) {
            $response = ['errors' => [$e->getMessage()]];
            return $this->requestMapper->dtoToResponse($response, 400);
        } catch (\JsonMapper_Exception $e) {
            $response = ['errors' => ['Error when parsing JSON: ' . $e->getMessage()]];
            return $this->requestMapper->dtoToResponse($response, 400);
        }
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
