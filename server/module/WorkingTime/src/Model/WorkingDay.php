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

class WorkingDay extends ArrayObject
{
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
        // TODO change to `DateTime` objects
        $this->id = !empty($data['id']) ? (int)$data['id'] : null;
        $this->userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $this->date = !empty($data['date']) ? $data['date'] : null;
        $this->begin = !empty($data['begin']) ? $data['begin'] : null;
        $this->end = !empty($data['end']) ? $data['end'] : null;
        $this->timeOff = !empty($data['time_off']) ? $data['time_off'] : null;
        $this->comment = !empty($data['comment']) ? $data['comment'] : null;
        $this->break = !empty($data['break']) ? (bool)$data['break'] : null;
        $this->afternoon = !empty($data['afternoon']) ? (bool)$data['afternoon'] : null;
        $this->afternoonBegin = !empty($data['afternoon_begin']) ? $data['afternoon_begin'] : null;
        $this->afternoonEnd = !empty($data['afternoon_end']) ? $data['afternoon_end'] : null;
    }

    public function getArrayCopy()
    {
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
