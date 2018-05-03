<?php

namespace SampleChat\Dtos;


class NewMessageRequest extends AbstractRequest
{
    /** @var string */
    public $value;

    /** @var int */
    public $toUserId;

    public function validate(): void
    {
        $this->checkIsSet('toUserId', $this->toUserId);
        $this->checkIsInt('toUserId', $this->toUserId);
        $this->checkIsSet('value', $this->value);
        $this->checkLength('value', $this->value, 1, 1000);
    }
}
