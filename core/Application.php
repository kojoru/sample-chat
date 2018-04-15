<?php

namespace SampleChat\Core;

use SampleChat\Controllers\UserController;

class Application
{
    function run()
    {
        $userController = new UserController();
        $router = new Router();
        $router->addRoute(new Route("/test", array($userController, "getCurrentUser")));

        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        $response = $router->run($request);
        if ($response != null) {
            \Http\Response\send($response);
        } else {
            if ($this->getCurrentUrl() == "/") {
                readfile(PUBLIC_DIR . "/index.html");
            } else {
                http_response_code(404);
                echo "404: " . htmlspecialchars($this->getCurrentUrl()) . " not found";
            }
        }


    }

    private function getCurrentUrl()
    {
        $path = urldecode(trim($_SERVER['REQUEST_URI']));
        if (($position = strpos($path, '?')) !== FALSE) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }
}
