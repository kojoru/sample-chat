<?php

namespace SampleChat\Controllers;

use GuzzleHttp\Psr7\Response;
use SampleChat\Dtos\UserRequest;
use SampleChat\Dtos\UserResponse;

class UserController
{
    /* @var \SampleChat\Database\DbConnection */
    private $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    public function getCurrentUser(): Response
    {
        return new Response(200, [], "Success!");
    }

    public function authoriseUser(UserRequest $request): UserResponse
    {
        $result = new UserResponse();
        $result->name = $request->name;
        $result->token = $this->db->login($request->name);

        return $result;
    }
}
