<?php

namespace SampleChat\Dtos;


class MessageListRequest extends AbstractRequest
{
    /** @var int */
    public $count;

    /** @var string */
    public $afterDate;

    /** @var string */
    public $beforeDate;

    /** @var int */
    public $userId;

    public function validate(): void
    {
        $this->checkIsDate('before_date', $this->beforeDate);
        $this->checkIsDate('after_date', $this->afterDate);
        $this->checkIsInt('user_id', $this->userId);
    }
}
