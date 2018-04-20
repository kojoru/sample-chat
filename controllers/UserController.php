<?php

namespace SampleChat\Controllers;

use GuzzleHttp\Psr7\Response;
use SampleChat\Dtos\UserRequest;
use SampleChat\Dtos\UserResponse;

class UserController
{
    public function getCurrentUser(): Response
    {
        return new Response(200, [], "Success!");
    }

    public function authoriseUser(UserRequest $request): UserResponse
    {
        $result = new UserResponse();
        $result->name = $request->name;

        return $result;
    }
}
