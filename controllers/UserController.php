<?php

namespace SampleChat\Controllers;

use GuzzleHttp\Psr7\Response;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\UserInList;
use SampleChat\Dtos\UserListResponse;
use SampleChat\Dtos\UserRequest;
use SampleChat\Dtos\UserResponse;

class UserController
{
    /* @var DbConnection */
    private $db;

    function __construct(DbConnection $db)
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

    public function getUserList(): UserListResponse
    {
        $result = new UserListResponse();
        $result->users = array();
        $users = $this->db->getAllUsers();
        foreach ($users as $user) {
            $userInList = new UserInList();
            $userInList->name = $user["Name"];
            $userInList->lastOnline = $user["LastOnlineDate"];
            array_push($result->users, $userInList);
        }

        return $result;
    }
}
