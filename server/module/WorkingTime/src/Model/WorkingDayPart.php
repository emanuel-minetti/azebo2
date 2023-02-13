<?php

namespace WorkingTime\Model;

use ArrayObject;
use AzeboLib\Saldo;
use DateTime;
use Laminas\Config\Factory;

class WorkingDayPart extends ArrayObject {
    public const TIME_FORMAT = 'H:i:s';

    public int $id;
    public int $workingDayId;
    public DateTime | null $begin;
    public DateTime | null $end;
    public bool $mobileWorking;

    public function exchangeArray(object|array $array): array {
        if (!is_array($array)) {
            $array = array($array);
        }
        $this->id = $array['id'];
        $this->workingDayId = $array['working_day_id'];
        $this->begin =  !empty($array['begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['begin']) : null;
        $this->end = !empty($array['end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['end']) : null;
        $this->mobileWorking = $array['mobile_working'] == 1;
        return array($this);
    }

    public function getArrayCopy(): array
    {
        return [
            'id' => $this->id,
            'working_day_id' => $this->workingDayId,
            'begin' => isset($this->begin) ? $this->begin->format(self::TIME_FORMAT) : null,
            'end' => isset($this->end) ? $this->end->format(self::TIME_FORMAT) : null,
            'mobile_working' => $this->mobileWorking ? 1 : 0,
        ];
    }

    public function __toString() {
        return json_encode($this->getArrayCopy());
    }

    public function getSaldo(): Saldo {
        if ($this->begin && $this->end) {
            return Saldo::createFromBeginAndEnd($this->begin, $this->end);
        } else {
            return Saldo::createFromHoursAndMinutes();
        }
    }

    public function getActualSaldo(): Saldo {
        $config = Factory::fromFile('./../server/config/times.config.php', true);
        $saldo = $this->getSaldo();
        if ($saldo->getHours() < $config->get('breakRequiredFromHours')) {
            return $saldo;
        } elseif ($saldo->getHours() == $config->get('breakRequiredFromHours')) {
            if ($saldo->getMinutes() <= $config->get('breakRequiredFromMinutes')) {
                return $saldo;
            }
        }
        // break required
        $break = Saldo::createFromHoursAndMinutes(0, $config->get('breakDuration'), false);
        $newSaldo = Saldo::getSum($saldo, $break);
        if ($saldo->getHours() < $config->get('longBreakRequiredFromHours')) {
            return $newSaldo;
        } elseif ($saldo->getHours() == $config->get('longBreakRequiredFromHours')) {
            if ($saldo->getMinutes() <= $config->get('longBreakRequiredFromMinutes')) {
                return $newSaldo;
            }
        }
        // long break required
        $longBreak = Saldo::createFromHoursAndMinutes(0, $config->get('longBreakDuration'), false);
        return Saldo::getSum($saldo, $longBreak);
    }
}