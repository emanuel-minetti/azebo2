<?php

namespace WorkingTime\Model;

use ArrayObject;
use DateTime;

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
        $this->mobileWorking = boolval($array['mobile_working']);
        return array($this);
    }

    public function getArrayCopy(): array
    {
        return [
            'id' => $this->id,
            'working_day_id' => $this->workingDayId,
            'begin' => isset($this->begin) ? $this->begin->format(self::TIME_FORMAT) : null,
            'end' => isset($this->end) ? $this->end->format(self::TIME_FORMAT) : null,
            'mobile_working' => $this->mobile_working ? 1 : 0,
        ];
    }

    public function __toString() {
        return json_encode($this->getArrayCopy());
    }
}