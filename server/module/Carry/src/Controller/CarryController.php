<?php /** @noinspection PhpUnused */

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
use Carry\Model\Carry;
use Carry\Model\CarryTable;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;
use WorkingRule\Model\WorkingRule;

class CarryController extends ApiController
{
    private $monthTable;
    private $carryTable;

    public function __construct(WorkingMonthTable $monthTable, CarryTable $carryTable)
    {
        $this->monthTable = $monthTable;
        $this->carryTable = $carryTable;
    }

    public function carryResultAction()
    {
        $this->prepare();
        if (AuthorizationService::authorize($this->request, $this->response, ['GET',])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $yearId = $this->params('year');
            $monthId = $this->params('month');
            $month = DateTime::createFromFormat(WorkingRule::DATE_FORMAT, "$yearId-$monthId-01");
            $resultMonth = $this->monthTable->getByUserIdAndMonth($userId, $month);
            $resultCarry = $this->carryTable->getByUserIdAndYear($userId, $month);
            $saldo = $resultCarry->saldo;
            $holidaysPrevious = $resultCarry->holidaysPreviousYear;
            $holidaysLeft = $resultCarry->holidays;
            $finalized = false;
            foreach ($resultMonth as $workingMonth) {
                // set finalized and continue if this month is already in the table
                if ($month->format('n') === $workingMonth->month->format('n')) {
                    $finalized = true;
                    continue;
                }
                $saldo = Saldo::getSum($saldo, $workingMonth->saldo);
                $holidays = $workingMonth->holidays;
                //if month can have holidays from previous year
                if ($workingMonth->month->format('n') <= Carry::PREVIOUS_HOLIDAYS_VALID_TO_MONTH) {
                    if ($holidaysPrevious >= $holidays) {
                        $holidaysPrevious -= $holidays;
                    } else {
                        $holidays -= $holidaysPrevious;
                        $holidaysPrevious = 0;
                        $holidaysLeft -= $holidays;
                    }
                } else {
                    $holidaysLeft -= $holidays;
                }
            }

            $resultArray = [
                'saldo_hours' => $saldo ? $saldo->getHours() : '0',
                'saldo_minutes' => $saldo ? $saldo->getMinutes() : '0',
                'saldo_positive' => $saldo ? $saldo->isPositive() : '0',
                'holidays_previous_year' => $holidaysPrevious,
                'holidays' => $holidaysLeft,
                'finalized' => $finalized,
            ];
            return $this->processResult($resultArray, $userId);
        } else {
            return $this->response;
        }
    }

    public function carryAction() {
        $this->prepare();
        if (AuthorizationService::authorize($this->request, $this->response, ['GET',])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $carry = $this->carryTable->getByUserId($userId);
            $resultArray = $carry ? $carry->getArrayCopy() : null;
            return $this->processResult($resultArray, $userId);
        } else {
            return $this->response;
        }
    }

    public function setCarryAction() {
        $this->prepare();
        $post = json_decode($this->httpRequest->getContent());
        if (AuthorizationService::authorize($this->request, $this->response, ['POST',])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            //$post
            return new JsonModel([
                'test' => "hallo",
                'post' =>  $post,
                'userId' => $userId,
            ]);
        } else {
            return $this->response;
        }
    }
}
