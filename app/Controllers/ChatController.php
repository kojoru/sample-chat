<?php

namespace SampleChat\Controllers;

use SampleChat\Core\Context;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\MessageInfo;
use SampleChat\Dtos\MessageListRequest;
use SampleChat\Dtos\MessageListResponse;
use SampleChat\Dtos\NewMessageRequest;
use SampleChat\Dtos\NewMessageResponse;

class ChatController
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

    public function addMessage(NewMessageRequest $request): NewMessageResponse
    {
        if ($request->toUserId == $this->context->user['Id']) {
            throw new \InvalidArgumentException('Can\'t send messages to yourself');
        }

        $toUser = $this->db->getUserById($request->toUserId);
        if (!$toUser) {
            throw new \InvalidArgumentException('User with id mentioned in toUserId does not exist');
        }

        $newMessage = $this->db->addMessage($this->context->user['Id'], $request->toUserId, $request->value);

        $result = new NewMessageResponse();
        $result->message = $this->dbMessageToMessageInfo($newMessage);
        return $result;
    }

    public function getMessageList(MessageListRequest $request): MessageListResponse
    {
        $count = $request->count;
        if (!$request->count || $request->count > 50) {
            $count = 50;
        }

        $messages = $this->db->getMessages(
            $count + 1,
            $this->context->user['Id'],
            $request->userId,
            $request->afterDate,
            $request->beforeDate
        );


        $result = new MessageListResponse();
        $result->messages = [];
        $result->hasMore = false;
        if (count($messages) > $count) {
            $result->hasMore = true;
            $messages = array_slice($messages, 0, $count);
        }

        foreach ($messages as $message) {
            array_push($result->messages, $this->dbMessageToMessageInfo($message));
        }

        return $result;
    }

    private function dbMessageToMessageInfo($dbMessage): MessageInfo
    {
        $result = new MessageInfo();
        $result->id = $dbMessage['Id'];
        $result->value = $dbMessage['Value'];
        $result->fromUserId = $dbMessage['FromUserId'];
        $result->toUserId = $dbMessage['ToUserId'];
        $result->date = $dbMessage['SentDate'];
        return $result;
    }
}
