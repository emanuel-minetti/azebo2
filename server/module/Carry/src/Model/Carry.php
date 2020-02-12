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

class Carry extends ArrayObject
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
     * @var DateTime year of the carry over
     */
    public $year;

    /**
     * @var Saldo the saldo of this year
     */
    public $saldo;

    /**
     * @var int the number of holidays left for the following year
     */
    public $holidays;

    /**
     * @var int the number of holidays left from this year
     */
    public $holidaysPreviousYear;

    public function exchangeArray($data)
    {
        $this->id = (int)$data['id'] ?? 0;
        $this->userId = (int)$data['user_id'] ?? 0;
        $this->year = !empty($data['year'])
            ? DateTime::createFromFormat(WorkingDay::DATE_FORMAT, $data['year']) : null;
        $this->saldo = !(empty($data['saldo_hours']) && empty($data['saldo_minutes']) && empty($data['saldo_positive']))
            ? Saldo::createFromHoursAndMinutes($data['saldo_hours'], $data['saldo_minutes'], $data['saldo_positive']) :
            Saldo::createFromHoursAndMinutes();
        $this->holidays = (int)$data['holidays'] ?? 0;
        $this->holidaysPreviousYear = (int)$data['holidays_previous_year'] ?? 0;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'year' => $this->year->format(WorkingDay::DATE_FORMAT),
            'saldo_hours' => $this->saldo->getHours(),
            'saldo_minutes' => $this->saldo->getMinutes(),
            'saldo_positive' => $this->saldo->isPositive(),
            'holidays' => $this->holidays,
            'holidays_previous_year' => $this->holidaysPreviousYear,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }

}
