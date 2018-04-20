<?php

namespace SampleChat\Controllers;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class IndexController
{
    public function index(): Response
    {
        return new Response(200, [], fopen(PUBLIC_DIR . '/index.html', 'r'));
    }

    public function notFound($requestBody, $query, Request $request): Response
    {
        return new Response(404, [], "404: " . htmlspecialchars($request->getUri()->getPath()) . " not found");
    }
}
