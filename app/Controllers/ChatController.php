<?php

namespace SampleChat\Controllers;

use SampleChat\Core\Context;
use SampleChat\Database\DbConnection;
use SampleChat\Dtos\MessageInfo;
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

        $newMessage = $this->db->addMessage($this->context->user["Id"], $request->toUserId, $request->value);

        $result = new NewMessageResponse();
        $result->message = new MessageInfo();
        $result->message->id = $newMessage["Id"];
        $result->message->value = $newMessage["Value"];
        $result->message->fromUserId = $newMessage["FromUserId"];
        $result->message->toUserId = $newMessage["ToUserId"];
        $result->message->date = $newMessage["SentDate"];

        return $result;
    }

    public function getMessageList($_, $query): MessageListResponse
    {
        $result = new MessageListResponse();

        //todo

        return $result;
    }
}
