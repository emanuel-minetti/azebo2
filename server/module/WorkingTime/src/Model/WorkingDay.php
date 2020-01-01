<?php
/**
 *
 * azebo2 is an application to print working time tables
 * Copyright(C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version .
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE . See the
 * GNU General Public License for more details .
 *
 * You should have received a copy of the GNU General Public License
 * along with this program .  If not, see < https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright(c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Model;


use ArrayObject;
use DateTime;

class WorkingDay extends ArrayObject
{
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @var int the primary kes of `WorkingDay`
     */
    public $id;
    /**
     * @var int foreign key to `User.php`
     */
    public $userId;
    /**
     * @var DateTime the date of the working day
     */
    public $date;
    /**
     * @var DateTime start of working time
     */
    public $begin;
    /**
     * @var DateTime end of working time
     */
    public $end;
    /**
     * @var string an enumerated value
     */
    public $timeOff;
    /**
     * @var string a free text field
     */
    public $comment;
    /**
     * @var bool whether a break was counted
     */
    public $break;
    /**
     * @var bool whether the working day was split
     */
    public $afternoon;
    /**
     * @var DateTime start of second part of working day
     */
    public $afternoonBegin;
    /**
     * @var DateTime end of second part of working day
     */
    public $afternoonEnd;

    public function exchangeArray($data)
    {
        $this->id = (int)$data['id'] ?? 0;
        $this->userId = (int)$data['user_id'] ?? 0;
        $this->date = !empty($data['date']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $data['date']) : null;
        $this->begin = !empty($data['begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $data['begin']) : null;
        $this->end = !empty($data['end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $data['end']) : null;
        $this->timeOff = $data['time_off'] ?? null;
        $this->comment = $data['comment'] ?? null;
        $this->break = (bool)$data['break'] ?? false;
        $this->afternoon = (bool)$data['afternoon'] ?? false;
        $this->afternoonBegin = !empty($data['afternoon_begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $data['afternoon_begin']) : null;
        $this->afternoonEnd = !empty($data['afternoon_end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $data['afternoon_end']) : null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'date' => $this->date->format(self::DATE_FORMAT),
            'begin' => $this->begin->format(self::TIME_FORMAT),
            'end' => $this->end->format(self::TIME_FORMAT),
            'time_off' => $this->timeOff,
            'comment' => $this->comment,
            'break' => (int)$this->break,
            'afternoon' => (int)$this->afternoon,
            'afternoon_begin' => $this->afternoonBegin ? $this->afternoonBegin->format(self::TIME_FORMAT) : null,
            'afternoon_end' => $this->afternoonEnd ? $this->afternoonEnd->format(self::TIME_FORMAT) : null,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }
}
