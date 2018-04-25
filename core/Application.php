<?php

namespace SampleChat\Core;

use JsonMapper;
use SampleChat\Controllers\IndexController;
use SampleChat\Controllers\UserController;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\UserRequest;

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

        $router->addRoute(new Route("/test", array($userController, "getCurrentUser")));
        $router->addRoute(new Route("/user", array($userController, "authoriseUser"), "POST", new UserRequest()));
        $router->addRoute(new Route("/users", array($userController, "getUserList")));
        $router->addRoute(new Route("/", array($indexController, "index")));
        $router->addDefaultRoute(new Route("", array($indexController, "notFound")));

        return $router;
    }

}
