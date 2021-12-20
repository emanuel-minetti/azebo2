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
     * Validates whether `$value` is in the list of time-offs. If is it and its value forbids 'begin' and 'end',
     * it validates that theese are not set.
     *
     * @param array $value an array containing `before`, `end` and `timeOff`.
     */
    public function isValid($value): bool
    {
        $timeOffs = Factory::fromFile('./../server/config/timeOffs.config.php');
        $timeOff = $value['timeOff'];
        if (!in_array($timeOff, $timeOffs)) return false;
        if ($timeOff == "urlaub" ||
            $timeOff == "gleitzeit" ||
            $timeOff == "azv" ||
            $timeOff == "gruen" ||
            $timeOff == "krank" ||
            $timeOff == "kind" ||
            $timeOff == "reise" ||
            $timeOff == "befr" ||
            $timeOff == "sonder" ||
            $timeOff == "bildung_url" ||
            $timeOff == "bildung") {
                if (isset($value['begin'])) return false;
                if (isset($value['end'])) return false;
        }
        return true;
    }
}
