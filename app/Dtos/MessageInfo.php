<?php

namespace SampleChat\Dtos;


class MessageInfo
{
    /** @var int */
    public $id;
    /** @var string */
    public $value;
    /** @var string */
    public $date;
    /** @var int */
    public $fromUserId;
    /** @var int */
    public $toUserId;

}