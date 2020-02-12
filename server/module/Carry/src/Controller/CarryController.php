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

    /** @noinspection PhpUnused */
    public function carryAction() {
        $resultMonth = $this->monthTable->getByUserIdAndMonth(1, DateTime::createFromFormat(WorkingDay::DATE_FORMAT, '2020-02-01'));
        $resultCarry = $this->carryTable->getByUserId(1);
        $resultArray = [];
        foreach ($resultMonth as $object) {
            $resultArray[] = $object->getArrayCopy();
        }
//        foreach ($resultCarry as $object) {
//            $resultArray[] = $object->getArrayCopy();
//        }
        $resultArray[] = $resultCarry[0]->getArrayCopy();
        //var_dump($resultCarry);
        return $this->processResult($resultArray, 1);
    }
}
