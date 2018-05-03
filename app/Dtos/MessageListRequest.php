<?php

namespace SampleChat\Dtos;


class MessageListRequest
{
    /** @var int */
    public $count;

    /** @var string */
    public $afterDate;

    /** @var string */
    public $beforeDate;

    /** @var int */
    public $userId;
}
