<?php

namespace SampleChat\Controllers;

use GuzzleHttp\Psr7\Response;

class UserController
{
    public function getCurrentUser(): Response
    {
        return new Response(200, [], "Success!");
    }
}