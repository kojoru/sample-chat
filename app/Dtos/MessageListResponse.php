<?php

namespace SampleChat\Dtos;


class MessageListResponse
{
    /* @var MessageInfo[] */
    public $messages;

    /** @var bool */
    public $hasMore;

}