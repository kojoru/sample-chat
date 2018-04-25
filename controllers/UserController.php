<?php

namespace SampleChat\Controllers;

use SampleChat\Database\DbConnection;
use SampleChat\Dtos\LoginRequest;
use SampleChat\Dtos\UserInList;
use SampleChat\Dtos\UserListResponse;
use SampleChat\Dtos\UserResponse;

class UserController
{
    /* @var DbConnection */
    private $db;

    function __construct(DbConnection $db)
    {
        $this->db = $db;
    }

    public function authoriseUser(LoginRequest $request): UserResponse
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
