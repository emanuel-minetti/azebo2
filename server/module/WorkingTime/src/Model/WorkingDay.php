<?php
/**
 * azebo2 is an application to print working-time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Model;

use ArrayObject;
use DateTime;

class WorkingDay extends ArrayObject
{
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    /** @var int the primary key of `WorkingDay` */
    public int $id;
    /** @var int foreign key to `User.php` */
    public int $userId;
    /** @var DateTime the date of the working day */
    public DateTime $date;
    /** @var ?DateTime start of working time */
    public ?DateTime $begin;
    /** @var ?DateTime end of working time */
    public ?DateTime $end;
    /** @var string an enumerated value */
    public string $timeOff;
    /** @var string a free text field */
    public string $comment;
    /** @var bool whether a break was counted */
    public bool $mobile_working;
    /** @var bool whether the working day was split */
    public bool $afternoon;
    /** @var ?DateTime start of second part of working day */
    public ?DateTime $afternoonBegin;
    /** @var ?DateTime end of second part of working day */
    public ?DateTime $afternoonEnd;

    public function exchangeArray($array)
    {
        $this->id = (int)$array['id'] ?? 0;
        $this->userId = (int)$array['user_id'] ?? 0;
        $this->date = !empty($array['date']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['date']) : null;
        $this->begin = !empty($array['begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['begin']) : null;
        $this->end = !empty($array['end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['end']) : null;
        $this->timeOff = $array['time_off'] ?? "";
        $this->comment = $array['comment'] ?? "";
        $this->mobile_working = (bool)$array['mobile_working'];
        $this->afternoon = (bool)$array['afternoon'] ?? false;
        $this->afternoonBegin = !empty($array['afternoon_begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['afternoon_begin']) : null;
        $this->afternoonEnd = !empty($array['afternoon_end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['afternoon_end']) : null;
    }

    public function getArrayCopy(): array
    {
        return [
            'id' => $this->id ?? null,
            'user_id' => $this->userId,
            'date' => $this->date->format(self::DATE_FORMAT),
            'begin' => isset($this->begin) ? $this->begin->format(self::TIME_FORMAT) : null,
            'end' => isset($this->end) ? $this->end->format(self::TIME_FORMAT) : null,
            'time_off' => $this->timeOff == "" ? null : $this->timeOff,
            'comment' => $this->comment == "" ? null: $this->comment,
            'mobile_working' => $this->mobile_working ? 1 : 0,
            'afternoon' => $this->afternoon ? 1 : 0,
            'afternoon_begin' => isset($this->afternoonBegin) ? $this->afternoonBegin->format(self::TIME_FORMAT) : null,
            'afternoon_end' => isset($this->afternoonEnd) ? $this->afternoonEnd->format(self::TIME_FORMAT) : null,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }
}
