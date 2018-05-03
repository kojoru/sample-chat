<?php

namespace SampleChat\Dtos;


class MessageListResponse
{
    /* @var MessageInfo[] */
    public $messages;

    /** @var bool */
    public $has_more;

}