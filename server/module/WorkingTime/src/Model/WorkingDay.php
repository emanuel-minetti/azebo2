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
    private const TIME_FORMAT = 'H:i:s';
    private const DATE_FORMAT = 'Y-m-d';

    public $id;
    public $userId;
    public $date;
    public $begin;
    public $end;
    public $timeOff;
    public $comment;
    public $break;
    public $afternoon;
    public $afternoonBegin;
    public $afternoonEnd;

    public function exchangeArray($data)
    {
        $this->id = (int)$data['id'] ?? 0;
        $this->userId = (int)$data['user_id'] ?? 0;
        $this->date = !empty($data['date']) ? DateTime::createFromFormat(self::DATE_FORMAT, $data['date']) : null;
        $this->begin = !empty($data['begin']) ? DateTime::createFromFormat(self::TIME_FORMAT, $data['begin']) : null;
        $this->end = !empty($data['end']) ? DateTime::createFromFormat(self::TIME_FORMAT, $data['end']) : null;
        $this->timeOff = $data['time_off'] ?? null;
        $this->comment = $data['comment'] ?? null;
        $this->break = (bool)$data['break'] ?? false;
        $this->afternoon = (bool)$data['afternoon'] ?? false;
        $this->afternoonBegin = !empty($data['afternoon_begin']) ? $data['afternoon_begin'] : null;
        $this->afternoonEnd = !empty($data['afternoon_end']) ? $data['afternoon_end'] : null;
    }

    public function getArrayCopy()
    {
        // TODO change from `DateTime` objects
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'date' => $this->date,
            'begin' => $this->begin,
            'end' => $this->end,
            'time_off' => $this->timeOff,
            'comment' => $this->comment,
            'break' => $this->break,
            'afternoon' => $this->afternoon,
            'afternoon_begin' => $this->afternoonBegin,
            'afternoon_end' => $this->afternoonEnd,
        ];
    }
}
