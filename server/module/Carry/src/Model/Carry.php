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

    public const PREVIOUS_HOLIDAYS_VALID_TO_MONTH = 9;

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

//    /**
//     * @var int the number of holidays left for the following year
//     */
//    public $holidays;
//
//    /**
//     * @var int the number of holidays left from this year
//     */
//    public $holidaysPreviousYear;

    public function exchangeArray($array)
    {
        $this->id = (int)$array['id'] ?? 0;
        $this->userId = (int)$array['user_id'] ?? 0;
        $this->year = !empty($array['year'])
            ? DateTime::createFromFormat(WorkingDay::DATE_FORMAT, $array['year']) : new DateTime();
        $this->saldo = !(empty($array['saldo_hours']) && empty($array['saldo_minutes']) && empty($array['saldo_positive']))
            ? Saldo::createFromHoursAndMinutes($array['saldo_hours'], $array['saldo_minutes'], $array['saldo_positive']) :
            Saldo::createFromHoursAndMinutes();
//        $this->holidays = (int)$array['holidays'] ?? 0;
//        $this->holidaysPreviousYear = (int)$array['holidays_previous_year'] ?? 0;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'year' => isset($this->year) ? $this->year->format(WorkingDay::DATE_FORMAT) : null,
            'saldo_hours' => $this->saldo->getHours(),
            'saldo_minutes' => $this->saldo->getMinutes(),
            'saldo_positive' => $this->saldo->isPositive(),
//            'holidays' => $this->holidays,
//            'holidays_previous_year' => $this->holidaysPreviousYear,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }

}
