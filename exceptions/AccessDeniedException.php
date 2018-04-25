<?php

namespace SampleChat\Exceptions;


use Exception;

class AccessDeniedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {

        parent::__construct($message, $code, $previous);
    }


}