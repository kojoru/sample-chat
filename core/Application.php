<?php

namespace SampleChat\Core;

use JsonMapper;
use SampleChat\Controllers\IndexController;
use SampleChat\Controllers\UserController;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\LoginRequest;
use SampleChat\Middlewares\CheckAuthMiddleware;
use SampleChat\Middlewares\CheckJsonMiddleware;

class Application
{
    /* @var RequestMapper */
    private $requestMapper;

    function run()
    {
        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        $router = $this->initializeRouter();
        $response = $router->run($request);
        \Http\Response\send($response);
    }

    private function initializeRouter(): Router
    {
        $context = new Context();
        $db = new DbConnection();

        $mapper = new JsonMapper();
        $this->requestMapper = new RequestMapper($mapper);
        $userController = new UserController($context, $db);
        $indexController = new IndexController();
        $router = new Router();

        $json = new CheckJsonMiddleware();
        $auth = new CheckAuthMiddleware($context, $db);

        $router->addRoute($this->createRoute("/login", array($userController, "authoriseUser"))
            ->withMethod("POST")
            ->withRequestTemplate(new LoginRequest())
            ->withMiddlewares([$json]));
        $router->addRoute($this->createRoute("/user", array($userController, "getUserList"))
            ->withMiddlewares([$json, $auth]));
        $router->addRoute($this->createRoute("/", array($indexController, "index")));
        $router->addDefaultRoute($this->createRoute("", array($indexController, "notFound")));

        return $router;
    }

    private function createRoute(string $path, callable $action): Route
    {
        return new Route($this->requestMapper, $path, $action);
    }

}
