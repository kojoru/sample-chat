<?php

namespace SampleChat\Dtos;


class NewMessageRequest
{
    /** @var string */
    public $value;

    /** @var int */
    public $toUserId;
}
