<?php

namespace SampleChat\Controllers;

use SampleChat\Core\Context;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\LoginRequest;
use SampleChat\Dtos\UserInList;
use SampleChat\Dtos\UserListResponse;
use SampleChat\Dtos\UserResponse;

class UserController
{
    /** @var Context */
    private $context;
    /** @var DbConnection */
    private $db;

    function __construct(Context $context, DbConnection $db)
    {
        $this->context = $context;
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
            $userInList->id = $user["Id"];
            $userInList->name = $user["Name"];
            $userInList->lastOnline = $user["LastOnlineDate"];
            if ($user["Name"] == $this->context->user["Name"]) {
                $userInList->isCurrentUser = true;
            }
            array_push($result->users, $userInList);
        }

        return $result;
    }
}
