<?php

namespace SampleChat\Dtos;


abstract class AbstractRequest
{
    private $date_regex = '^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$';
    protected $validation_problems = [];

    public abstract function validate(): void;

    public function isValid(): bool
    {
        return count($this->validation_problems) == 0;
    }

    public function getValidationProblems(): array
    {
        return $this->validation_problems;
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
        if (!preg_match($this->date_regex, $value)) {
            $this->addValidationProblem('Date parameter ' . $fieldName . 'is not set to a date.');
        }
    }

    protected function checkIsInt($fieldName, $value)
    {
        if (is_null($value)) {
            return;
        }
        if (!is_int($value)) {
            $this->addValidationProblem('Integer parameter ' . $fieldName . 'is not set to an integer.');
        }
    }

    private function addValidationProblem(string $problem): void
    {
        array_push($this->validation_problems, $problem);
    }
}