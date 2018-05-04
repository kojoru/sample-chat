<?php

namespace SampleChat\Dtos;


abstract class AbstractRequest
{
    // taken from https://www.myintervals.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
    private $dateRegex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
    protected $validationProblems = [];

    public abstract function validate(): void;

    public function isValid(): bool
    {
        return count($this->validationProblems) == 0;
    }

    public function getValidationProblems(): array
    {
        return $this->validationProblems;
    }

    protected function checkLength($fieldName, $value, $min = null, $max = null): void
    {
        if (is_null($value)) {
            return;
        }
        if ($min && strlen($value) < $min) {
            $this->addValidationProblem($fieldName . ' is shorter than its minimum of ' . $min . ' symbols');
        }
        if ($max && strlen($value) > $max) {
            $this->addValidationProblem($fieldName . ' is longer than its maximum of ' . $max . ' symbols');
        }
    }

    protected function checkIsSet($fieldName, $value)
    {
        if (is_null($value)) {
            $this->addValidationProblem('Mandatory field ' . $fieldName . ' is not set.');
        }
    }

    protected function checkIsDate($fieldName, $value)
    {
        if (is_null($value)) {
            return;
        }
        if (!preg_match($this->dateRegex, $value)) {
            $this->addValidationProblem('Date parameter ' . $fieldName . ' is not set to a date.');
        }
    }

    protected function checkIsInt($fieldName, $value)
    {
        if (is_null($value)) {
            return;
        }
        if (!ctype_digit((string)$value)) {
            $this->addValidationProblem('Integer parameter ' . $fieldName . ' is not set to an integer.');
        }
    }

    private function addValidationProblem(string $problem): void
    {
        array_push($this->validationProblems, $problem);
    }
}