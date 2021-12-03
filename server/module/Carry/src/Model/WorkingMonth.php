<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */


namespace Carry\Model;


use ArrayObject;

use AzeboLib\Saldo;
use DateTime;
use WorkingTime\Model\WorkingDay;

class WorkingMonth extends ArrayObject
{
    /**
     * @var int the primary key of the table
     */
    public $id;

    /**
     * @var int foreign key to `User.php`
     */
    public $userId;

    /**
     * @var DateTime date of the month
     */
    public $month;

    /**
     * @var Saldo the saldo of this month
     */
    public $saldo;

    /**
     * @var int the number of holidays taken in this month
     */
    public $holidays;

    /**
     * @var int the number of 'working time reduction' (AZV) days taken this month
     */
    public $workingTimeReduction;

    /**
     * @var boolean whether the working time sheet for this month is archived
     */
    public $archived;

    /**
     * @var boolean whether this month is already carried over to table carry
     */
    public $carried;

    public function exchangeArray($array)
    {
        $this->id = (int) $array['id'] ?? 0;
        $this->userId = (int) $array['user_id'] ?? 0;
        $this->month = !empty($array['month'])
            ? DateTime::createFromFormat(WorkingDay::DATE_FORMAT, $array['month']) : null;
        $this->saldo = !(empty($array['saldo_hours']) && empty($array['saldo_minutes']) && empty($array['saldo_positive']))
            ? Saldo::createFromHoursAndMinutes($array['saldo_hours'], $array['saldo_minutes'], $array['saldo_positive']) :
            Saldo::createFromHoursAndMinutes();
        $this->holidays = (int) $array['holidays'] ?? 0;
        $this->workingTimeReduction = (int) $array['working_time_reduction'];
        $this->archived = (int) $array['archived'] ?? 0;
        $this->carried = (int) $array['carried'] ?? 0;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'month' => $this->month->format(WorkingDay::DATE_FORMAT),
            'saldo_hours' => $this->saldo->getHours(),
            'saldo_minutes' => $this->saldo->getMinutes(),
            'saldo_positive' => $this->saldo->isPositive(),
            'holidays' => $this->holidays,
            'working_time_reduction' => $this->workingTimeReduction,
            'archived' => $this->archived,
            'carried' => $this->carried,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }

}
