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
use Carry\Model\WorkingMonthTable;
use DateTime;
use WorkingTime\Model\WorkingDay;

class CarryController extends ApiController
{
    private $monthTable;

    public function __construct(WorkingMonthTable $monthTable)
    {
        $this->monthTable = $monthTable;
    }

    /** @noinspection PhpUnused */
    public function carryAction() {
        $result = $this->monthTable->getByUserIdAndMonth(1, DateTime::createFromFormat(WorkingDay::DATE_FORMAT, '2020-02-01'));
        $resultArray = [];
        foreach ($result as $object) {
            $resultArray[] = $object->getArrayCopy();
        }
        return $this->processResult($resultArray, 1);
//        return new JsonModel([
//            'text' => "Hallo"
//        ]);
    }
}
