<?php

namespace Validation;

use DateTime;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Service\log\AzeboLog;

class BeginBeforeEndValidator extends AbstractValidator
{
    const NOT_BEFORE = 'notBefore';

    protected $messageTemplates = [
        self::NOT_BEFORE => "'begin' is not before 'end'",
    ];

    /**
     * Validates whether one `DateTime` (begin) is before the other `DateTime` (end).
     *
     * The two `DateTime`s should be given as an array with keys 'begin' and 'end'.
     *
     * @param array $value an array containing `before` and `end`.
     */
    public function isValid($value): bool
    {
        return $value['begin'] instanceof DateTime && $value['end'] instanceof DateTime && $value['begin'] < $value['end'];
    }
}