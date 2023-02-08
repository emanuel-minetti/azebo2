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
use AzeboLib\Saldo;
use Carry\Model\WorkingMonth;
use Carry\Model\WorkingMonthTable;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
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

    public function __construct(AzeboLog $logger,
                                WorkingDayTable $dayTable,
                                WorkingMonthTable $monthTable,
                                WorkingRuleTable $ruleTable)
    {
        parent::__construct($logger);
        $this->dayTable = $dayTable;
        $this->monthTable = $monthTable;
        $this->ruleTable = $ruleTable;
    }

    public function monthAction(): JsonModel|Response {
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse)) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $arrayOfWorkingDays = $this->dayTable->getByUserIdAndMonth($userId, $month);
            $resultArray = [];
            foreach ($arrayOfWorkingDays as $element) {
                $copy = $element->getArrayCopy();
                $partsCopy = [];
                foreach ($copy['day_parts'] as $part) {
                    $partsCopy[] = $part->getArrayCopy();
                }
                $copy['day_parts'] = $partsCopy;
                $resultArray[] = $copy;
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
                $day->dayParts = [];
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
//            if (isset($post->_begin)) {
//                $begin = DateTime::createFromFormat("H:i:s+", $post->_begin);
//                if (!$begin) return $this->invalidRequest;
//                $day->begin = $begin;
//            } else {
//                $day->begin = null;
//            }
//            if (isset($post->_end)) {
//                $end = DateTime::createFromFormat("H:i:s+", $post->_end);
//                if (!$end) return $this->invalidRequest;
//                $day->end = $end;
//            } else {
//                $day->end = null;
//            }

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
                    $day->dayParts[] = $dayPart;
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
            $workingMonth =  $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0]
                ?? new WorkingMonth();
            $workingDays = $this->dayTable->getByUserIdAndMonth($userId, $month);
            $rules = $this->ruleTable->getByUserIdAndMonth($userId, $month);
            try {
                $holidays = HolidayService::getHolidays($month->format('Y'));
            } catch (Exception $e) {
                $this->httpResponse->setContent($e->getMessage());
                return $this->httpResponse;
            }

            // set working day rules
            /** @var WorkingDay $day */
            foreach ($workingDays as $day) {
                foreach ($rules as $rule) {
                    if ($rule->isValid($day->date)) {
                        $day->rule = $rule;
                        break;
                    }
                }
            }

            // validate month
            $missing = [];
            $firstOfMonth = new DateTime();
            $firstOfNextMonth = new DateTime();
            $firstOfMonth->setDate($month->format('Y'), $month->format('n'), 1);
            $firstOfNextMonth->setDate($month->format('Y'), $month->format('n'), 1);
            $firstOfNextMonth->add(new DateInterval('P1M'));
            $oneDay = new DateInterval('P1D');
            $allMonthDays = new DatePeriod($firstOfMonth, $oneDay, $firstOfNextMonth);
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
                                    $dayParts = $dayWorkingDay->dayParts;
                                    $dayWorkingDay->saldo = array_reduce(
                                        $dayParts,
                                        function (Saldo $prev, WorkingDayPart $curr) {
                                            return Saldo::getSum($prev, $curr->getActualSaldo());
                                        },
                                        Saldo::createFromHoursAndMinutes());
                                    if ($dayWorkingDay->saldo->getHours() === 0 &&
                                        $dayWorkingDay->saldo->getMinutes() === 0) {
                                        $missing[] = $monthDay->format('j');
                                    }
                            }
                        } else {
                            $missing[] = $monthDay->format('j');
                        }
                    }
                }
            }
            // compute month saldo
            $saldo = array_reduce($workingDays, function (Saldo $prev, WorkingDay $curr) use ($userId, $month) {
                switch ($curr->timeOff) {
                    case '':
                    case "ausgleich":
                    case 'lang':
                        $target = $curr->rule ? $curr->rule->getTarget() / 1000 / 60 : 0;
                        $targetSaldo = Saldo::createFromHoursAndMinutes(0, $target, false);
                        $currentSaldo = Saldo::getSum($curr->saldo, $targetSaldo);
                        break;
                    case 'gleitzeit':
                        $target = $curr->rule ? $curr->rule->getTarget() / 1000 / 60 : 0;
                        $currentSaldo = Saldo::createFromHoursAndMinutes(0, $target, false);
                        break;
                    case 'zusatz':
                        $currentSaldo = $curr->saldo;
                        break;
                    default:
                        $currentSaldo = Saldo::createFromHoursAndMinutes();
                }
                return Saldo::getSum($prev, $currentSaldo);
            }, Saldo::createFromHoursAndMinutes());
            // update db
            // TODO implement

            // send result
            $result = [
                'text' => 'Hallo Welt!',
                'db' => $workingMonth->getArrayCopy(),
                'saldo' =>  (string) $saldo,
                'missing' => $missing,
            ];
            return $this->processResult($result, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }
}
