<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Carry\Controller;

use AzeboLib\ApiController;
use AzeboLib\Saldo;
use Carry\Model\CarryTable;
use Carry\Model\WorkingMonthTable;
use DateTime;
use WorkingTime\Model\WorkingDay;

class CarryController extends ApiController
{
    private $monthTable;
    private $carryTable;

    public function __construct(WorkingMonthTable $monthTable, CarryTable $carryTable)
    {
        $this->monthTable = $monthTable;
        $this->carryTable = $carryTable;
    }

    //TODO return the resulting carry over for this month!!

    /** @noinspection PhpUnused */
    public function carryResultAction()
    {
        $resultMonth = $this->monthTable->getByUserIdAndMonth(
            1, DateTime::createFromFormat(WorkingDay::DATE_FORMAT, '2020-02-01'));
        $resultCarry = $this->carryTable->getByUserIdAndYear(
            1, DateTime::createFromFormat(WorkingDay::DATE_FORMAT, '2020-02-01'));
        $saldo = $resultCarry->saldo;
        foreach ($resultMonth as $workingMonth) {
            $saldo = Saldo::getSum($saldo, $workingMonth->saldo);
        }
        $resultArray = [
            'saldo_hours' => $saldo->getHours(),
            'saldo_minutes' => $saldo->getMinutes(),
            'saldo_positive' => $saldo->isPositive(),
        ];
        //TODO remove debugging
//        $resultArray = [];
//        $resultArray[] = $resultCarry->getArrayCopy();
//        foreach ($resultMonth as $workingMonth) {
//            $resultArray[] = $workingMonth->getArrayCopy();
//        }
        return $this->processResult($resultArray, 1);
    }
}
