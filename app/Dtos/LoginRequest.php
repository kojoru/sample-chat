<?php

namespace SampleChat\Dtos;


class LoginRequest extends AbstractRequest
{
    /* @var string */
    public $name;

    public function validate(): void
    {
        $this->checkIsSet('name', $this->name);
        $this->checkLength('name', $this->name, 1, 50);
    }
}
