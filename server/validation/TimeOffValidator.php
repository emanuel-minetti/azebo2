<?php

namespace Validation;

use Laminas\Config\Factory;
use Laminas\Validator\AbstractValidator;
use Service\log\AzeboLog;

class TimeOffValidator extends AbstractValidator
{
    const NOT_IN_LIST = 'notIn';

    protected array $messageTemplates = [
        self::NOT_IN_LIST => '"value" is not in list',
    ];

    /**
     * Validates whether `$value` is in list.
     */
    public function isValid($value): bool
    {
        $timeOffs = Factory::fromFile('./../server/config/timeOffs.config.php');
        return in_array($value, $timeOffs);
    }
}
