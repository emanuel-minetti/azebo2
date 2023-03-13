<?php /** @noinspection PhpUnused */

/**
 * azebo2 is an application to print working timetables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Controller;

use AzeboLib\ApiController;
use AzeboLib\DaysOfMonth;
use AzeboLib\Saldo;
use Carry\Model\CarryTable;
use Carry\Model\WorkingMonth;
use Carry\Model\WorkingMonthTable;
use DateTime;
use Exception;
use Laminas\Config\Factory;
use Laminas\Http\Response;
use Laminas\Validator\StringLength;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;
use Service\HolidayService;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRuleTable;
use WorkingTime\Model\WorkingDay;
use WorkingTime\Model\WorkingDayPart;
use WorkingTime\Model\WorkingDayTable;

class WorkingTimeController extends ApiController
{
    public const TIME_FORMAT = 'H:i';

    private WorkingDayTable $dayTable;
    private WorkingMonthTable $monthTable;
    private WorkingRuleTable $ruleTable;
    private CarryTable $carryTable;

    public function __construct(AzeboLog $logger,
                                WorkingDayTable $dayTable,
                                WorkingMonthTable $monthTable,
                                WorkingRuleTable $ruleTable,
                                CarryTable $carryTable)
    {
        parent::__construct($logger);
        $this->dayTable = $dayTable;
        $this->monthTable = $monthTable;
        $this->ruleTable = $ruleTable;
        $this->carryTable = $carryTable;
    }

    public function monthAction(): JsonModel|Response {
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse)) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $arrayOfWorkingDays = $this->dayTable->getByUserIdAndMonth($userId, $month);
            $workingMonth = $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0] ?
                $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0]->getArrayCopy()  : null;
            $resultArray = [
                'days' => [],
                'month' => $workingMonth,
            ];
            foreach ($arrayOfWorkingDays as $element) {
                $copy = $element->getArrayCopy();
                $partsCopy = [];
                foreach ($copy['day_parts'] as $part) {
                    $partsCopy[] = $part->getArrayCopy();
                }
                $copy['day_parts'] = $partsCopy;
                $resultArray['days'][] = $copy;
            }
            return $this->processResult($resultArray, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }

    public function setDayAction(): JsonModel|Response {
        $this->prepare();
        $post = json_decode($this->httpRequest->getContent());

        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ['POST'])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            if (!isset($post->_id) || !is_numeric($post->_id)) return $this->invalidRequest;
            $id = $post->_id;

            if ($id != 0) {
                $day = $this->dayTable->find($id);
                if (!$day) return $this->invalidRequest;
            } else {
                if (!isset($post->_date)) return $this->invalidRequest;
                $date = DateTime::createFromFormat("D M d Y", $post->_date);
                if (!$date) return $this->invalidRequest;
                $day = new WorkingDay();
                $day->date = $date;
                $day->userId = $userId;
                $day->id = 0;
            }

//            if (isset($post->_begin) && isset($post->_end)) {
//                // validate
//                $bbeValidator = new BeginBeforeEndValidator();
//                $value = [
//                    'begin' => $day->begin,
//                    'end' => $day->end
//                ];
//                if (!$bbeValidator->isValid($value)) return $this->invalidRequest;
//                $config = Factory::fromFile('./../server/config/times.config.php', true);
//                try {
//                    $breakRequiredFrom = new DateInterval(
//                        'PT' . $config->get('breakRequiredFromHours') . 'H'
//                        . $config->get('breakRequiredFromMinutes') . 'M');
//                    $longBreakRequiredFrom = new DateInterval(
//                        'PT' . $config->get('longBreakRequiredFromHours') . 'H'
//                    . $config->get('longBreakRequiredFromMinutes') . 'M');
//                    $totalTime = $day->end->diff($day->begin, true);
//                    $now = new DateTime();
//                    if ((clone $now)->add($totalTime) > (clone $now)->add($longBreakRequiredFrom)) {
//                        $day->break = new DateInterval("PT" . $config->get('longBreakDuration') . "M");
//                    } elseif ((clone $now)->add($totalTime) > (clone $now)->add($breakRequiredFrom)) {
//                        $day->break = new DateInterval("PT" . $config->get('breakDuration') . "M");
//                    } else {
//                        $day->break = new DateInterval("PT0M");
//                    }
//                } catch (Exception $ignored) {
//                }
//            }
            if (isset($post->_timeOff)) {
//                $toValidator = new TimeOffValidator();
//                $value = [
//                    'begin' => $day->begin,
//                    'end' => $day->end,
//                    'timeOff' => $post->_timeOff,
//                ];
//                if (!$toValidator->isValid($value)) return $this->invalidRequest;
                $day->timeOff = $post->_timeOff;
            } else {
                $day->timeOff = "";
            }
            if (isset($post->_comment)) {
                $slValidator = new StringLength(['max' => 120]);
                if (!$slValidator->isValid($post->_comment)) return $this->invalidRequest;
                $day->comment = $post->_comment;
            } else {
                $day->comment = "";
            }

            if (isset($post->_day_parts)) {
                if (!is_array($post->_day_parts))
                    return $this->invalidRequest;
                $day->setDayParts([]);
                foreach ($post->_day_parts as $part) {
                    if (!isset($part->_id) || !is_numeric($part->_id))
                        return $this->invalidRequest;
                    $dayPart = new WorkingDayPart();
                    $dayPart->id = $part->_id;
                    $dayPart->workingDayId = $id;
                    $dayPart->begin = isset($part->_begin) ?
                        DateTime::createFromFormat(self::TIME_FORMAT, $part->_begin) : null;
                    $dayPart->end = isset($part->_end) ?
                        DateTime::createFromFormat(self::TIME_FORMAT, $part->_end) : null;
                    $dayPart->mobileWorking = $part->_mobileWorking;
                    $day->addDayPart($dayPart);
                }
            }
            $this->dayTable->upsert($day);
            return $this->processResult($day->getArrayCopy(), $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }

    public function closeMonthAction(): JsonModel|Response {
        $this->prepare();
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ['POST'])) {
            // gather data
            $userId = $this->httpRequest->getQuery()->user_id;
            $yearParam = $this->params('year');
            $monthParam = $this->params('month');
            $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
            $workingMonth =  $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0];
            if ($workingMonth) {
                $this->monthTable->delete($workingMonth);
                $result = [
                    'ok' => true,
                    'month' => null,
                ];
                return $this->processResult($result, $userId);
            } else {
                $workingMonth = new WorkingMonth([
                    'user_id' => $userId,
                    'month' => $month->format(WorkingDay::DATE_FORMAT),
                ]);
            }
            $workingDays = $this->dayTable->getByUserIdAndMonth($userId, $month);
            $rules = $this->ruleTable->getByUserIdAndMonth($userId, $month);
            try {
                $holidays = HolidayService::getHolidays($month->format('Y'));
            } catch (Exception $e) {
                $this->httpResponse->setContent($e->getMessage());
                return $this->httpResponse;
            }

            // validate month
            $missing = [];
            $allMonthDays = DaysOfMonth::get($month);
            foreach ($allMonthDays as $monthDay) {
                // test for weekend and holiday
                $weekday = $monthDay->format('N');
                $monthDayDateString = $monthDay->format(WorkingDay::DATE_FORMAT);
                $monthDayHoliday = null;
                foreach ($holidays as $holiday) {
                    if ($holiday['date'] === $monthDayDateString) {
                        $monthDayHoliday = $holiday;
                        break;
                    }
                }
                if (!($weekday == 6 || $weekday == 7 || $monthDayHoliday)) {
                    $dayRule = null;
                    foreach ($rules as $rule) {
                        if ($rule->isValid($monthDay)) {
                            $dayRule = $rule;
                            break;
                        }
                    }
                    if ($dayRule) {
                        /** @var WorkingDay | null $dayWorkingDay */
                        $dayWorkingDay = null;
                        foreach ($workingDays as $workingDay) {
                            if ($workingDay->date->format('j') === $monthDay->format('j')) {
                                $dayWorkingDay = $workingDay;
                                break;
                            }
                        }
                        if ($dayWorkingDay) {
                            switch ($dayWorkingDay->timeOff) {
                                case '':
                                case "ausgleich":
                                case 'lang':
                                case 'zusatz':
                                    if ($dayWorkingDay->getActualTime()->getHours() === 0 &&
                                        $dayWorkingDay->getActualTime()->getMinutes() === 0) {
                                        $missing[] = $monthDay->format('j');
                                    }
                            }
                        } else {
                            $missing[] = $monthDay->format('j');
                        }
                    }
                }
            }

            if (sizeof($missing) === 0) {
                // compute month saldo
                $saldo = array_reduce($workingDays, function (Saldo $prev, WorkingDay $curr) use ($userId, $month) {
                    $saldo = $curr->getSaldo() ?? Saldo::createFromHoursAndMinutes();
                    return Saldo::getSum($prev, $saldo);
                }, Saldo::createFromHoursAndMinutes());

                // get capping limit
                $lastRule = end($rules);
                $percentage = $lastRule->percentage;
                $config = Factory::fromFile('./../server/config/times.config.php', true);
                $cappingLimitMinutes = $config->get('cappingLimit');
                $cappingLimitMinutes = ceil($cappingLimitMinutes * $percentage / 100);
                $cappingLimit = Saldo::createFromHoursAndMinutes(0, $cappingLimitMinutes, false);

                // use capping limit
                $workingMonths = $this->monthTable->getByUserIdAndMonth($userId, $month);
                $carry = $this->carryTable->getByUserIdAndYear($userId, $month);
                $oldSaldo = array_reduce($workingMonths, function (Saldo $prev,WorkingMonth $curr) {
                    return Saldo::getSum($prev, $curr->saldo);
                }, $carry->saldo);
                $totalSaldo = Saldo::getSum($oldSaldo, $saldo);
                $difference = Saldo::getSum($totalSaldo, $cappingLimit);
                if ($difference->isPositive()) {
                    $difference = Saldo::createFromHoursAndMinutes(
                        $difference->getHours(), $difference->getMinutes(), false
                    );
                    $saldo = Saldo::getSum($saldo, $difference);
                    $workingMonth->saldoCapped = true;
                } else {
                    $workingMonth->saldoCapped = false;
                }

                // update db
                $workingMonth->saldo = $saldo;
                $newMonth = $this->monthTable->insert($workingMonth);
                $result = [
                    'ok' => true,
                    'month' => $newMonth->getArrayCopy(),
                ];
            } else {
                $result = [
                    'ok' => false,
                    'missing' => $missing,
                ];
            }

            // send result
            return $this->processResult($result, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }
}
