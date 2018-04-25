<?php

namespace SampleChat\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SampleChat\Core\Context;
use SampleChat\Database\DbConnection;
use SampleChat\Exceptions\AccessDeniedException;

class CheckAuthMiddleware implements MiddlewareInterface
{
    /** @var Context */
    private $context;

    /** @var DbConnection */
    private $db;

    function __construct(Context $context, DbConnection $db)
    {
        $this->context = $context;
        $this->db = $db;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeader("Authorization")[0];
        if (!$authHeader) {
            throw new AccessDeniedException("Please add an Authorization header with the bearer token");
        }

        if (!preg_match('/Bearer ([a-zA-Z0-9]*)/', $authHeader, $regexResult)) {
            throw new AccessDeniedException(
                "Unparseable Authorization header. Please ensure that it has format 'Bearer [token]'");
        }

        $user = $this->db->authoriseToken($regexResult[1]);
        if (!$user) {
            throw new AccessDeniedException(
                "Incorrect authorization token");
        }

        $this->context->user = $user;

        return $handler->handle($request);
    }
}
