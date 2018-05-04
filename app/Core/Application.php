<?php

namespace SampleChat\Core;

use GuzzleHttp\Psr7\Response;
use JsonMapper;
use SampleChat\Controllers\ChatController;
use SampleChat\Controllers\IndexController;
use SampleChat\Controllers\UserController;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\LoginRequest;
use SampleChat\Dtos\MessageListRequest;
use SampleChat\Dtos\NewMessageRequest;
use SampleChat\Middlewares\CheckAuthMiddleware;
use SampleChat\Middlewares\CheckJsonMiddleware;

class Application
{
    /* @var RequestMapper */
    private $requestMapper;

    function run()
    {
        try {
            $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
            $router = $this->initializeRouter();
            $response = $router->run($request);
            \Http\Response\send($response);
        } catch (\Throwable $e) {
            \Http\Response\send(new Response(500, [], '500: server error. Try again later or contact the developer.'));
            throw $e;
        }
    }

    private function initializeRouter(): Router
    {
        $context = new Context();
        $db = new DbConnection();

        $mapper = new JsonMapper();
        $this->requestMapper = new RequestMapper($mapper);
        $userController = new UserController($context, $db);
        $chatController = new ChatController($context, $db);
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
        $router->addRoute($this->createRoute("/message", array($chatController, "addMessage"))
            ->withMethod("POST")
            ->withRequestTemplate(new NewMessageRequest())
            ->withMiddlewares([$json, $auth]));
        $router->addRoute($this->createRoute("/message", array($chatController, "getMessageList"))
            ->withMethod("GET")
            ->withRequestTemplate(new MessageListRequest())
            ->withMiddlewares([$auth]));
        $router->addRoute($this->createRoute("/", array($indexController, "index")));
        $router->addDefaultRoute($this->createRoute("", array($indexController, "notFound")));

        return $router;
    }

    private function createRoute(string $path, callable $action): Route
    {
        return new Route($this->requestMapper, $path, $action);
    }

}
