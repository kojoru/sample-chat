<?php

namespace SampleChat\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckJsonMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * This is essentially a security check: there's no way a cross-origin request can set this header in that
         * fashion. As a result, we are protected from CSQR while simply checking for headers that have to be sent in
         * this case anyway.
         *
         * Now, this is obviously way too restrictive (e. g. there are other json mime-types, and Content-Type can be
         * more complex than that) but since it's not a public API and a sample prototype, that'll do.
         */
        if ($request->getHeader("Content-Type")[0] != "application/json") {
            throw new \InvalidArgumentException(
                "This path only supports JSON requests. Please add Content-Type: application/json to the headers");
        }

        return $handler->handle($request);
    }
}