<?php

namespace SampleChat\Core;

use JsonMapper;
use SampleChat\Controllers\IndexController;
use SampleChat\Controllers\UserController;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\LoginRequest;

class Application
{
    function run()
    {
        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        $router = $this->initializeRouter();
        $response = $router->run($request);
        \Http\Response\send($response);
    }

    private function initializeRouter(): Router
    {
        $db = new DbConnection();

        $mapper = new JsonMapper();
        $requestMapper = new RequestMapper($mapper);
        $userController = new UserController($db);
        $indexController = new IndexController();
        $router = new Router($requestMapper);

        $router->addRoute(new Route("/login", array($userController, "authoriseUser"), "POST", new LoginRequest()));
        $router->addRoute(new Route("/user", array($userController, "getUserList")));
        $router->addRoute(new Route("/", array($indexController, "index")));
        $router->addDefaultRoute(new Route("", array($indexController, "notFound")));

        return $router;
    }

}
