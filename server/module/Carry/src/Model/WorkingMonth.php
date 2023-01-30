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
use ReturnTypeWillChange;
use WorkingTime\Model\WorkingDay;

class WorkingMonth extends ArrayObject
{
    /**
     * @var int the primary key of the table
     */
    public int $id;

    /**
     * @var int foreign key to `User.php`
     */
    public int $userId;

    /**
     * @var DateTime date of the month
     */
    public DateTime $month;

    /**
     * @var Saldo the saldo of this month
     */
    public Saldo $saldo;

    /**
     * @var boolean whether the working time sheet for this month is archived
     */
    public bool $archived;

    /**
     * @var boolean whether this month is already carried over to table carry
     */
    public bool $carried;

    #[ReturnTypeWillChange] public function exchangeArray($array): void {
        $this->id = (int) $array['id'] ?? 0;
        $this->userId = (int) $array['user_id'] ?? 0;
        $this->month = !empty($array['month'])
            ? DateTime::createFromFormat(WorkingDay::DATE_FORMAT, $array['month']) : null;
        $this->saldo = !(empty($array['saldo_hours']) && empty($array['saldo_minutes']) && empty($array['saldo_positive']))
            ? Saldo::createFromHoursAndMinutes($array['saldo_hours'], $array['saldo_minutes'], $array['saldo_positive']) :
            Saldo::createFromHoursAndMinutes();
        $this->archived = (int) $array['archived'] ?? 0;
        $this->carried = (int) $array['carried'] ?? 0;
    }

    public function getArrayCopy(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'month' => $this->month->format(WorkingDay::DATE_FORMAT),
            'saldo_hours' => $this->saldo->getHours(),
            'saldo_minutes' => $this->saldo->getMinutes(),
            'saldo_positive' => $this->saldo->isPositive(),
            'archived' => $this->archived,
            'carried' => $this->carried,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }

}
