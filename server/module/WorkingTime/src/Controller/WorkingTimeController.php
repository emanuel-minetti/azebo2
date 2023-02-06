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
use DateTime;
use Laminas\Http\Response;
use Laminas\Validator\StringLength;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRule;
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
            $userId = $this->httpRequest->getQuery()->user_id;
            $yearParam = $this->params('year');
            $monthParam = $this->params('month');
            $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
            $workingMonth =  $this->monthTable->getByUserIdAndMonth($userId, $month, false)[0]
                ?? new WorkingMonth();
            $workingDays = $this->dayTable->getByUserIdAndMonth($userId, $month);
            $saldo = array_reduce($workingDays, function (Saldo $prev, WorkingDay $curr) use ($userId, $month) {
                $rules = $this->ruleTable->getByUserIdAndMonth($userId, $month);
                $dayRule = null;
                /** @var WorkingRule $rule */
                foreach ($rules as $rule) {
                    if ($rule->isValid($curr->date)) {
                        $dayRule = $rule;
                        break;
                    }
                }
                $timeOff = $curr->timeOff;
                if (!($timeOff === 'urlaub' ||
                    $timeOff === 'azv' ||
                    $timeOff === 'krank' ||
                    $timeOff === 'kind' ||
                    $timeOff === 'da_krank' ||
                    $timeOff === 'reise' ||
                    $timeOff === 'befr' ||
                    $timeOff === 'sonder' ||
                    $timeOff === 'da_befr' ||
                    $timeOff === 'bildung_url' ||
                    $timeOff === 'bildung')) {
                    $target = $dayRule ? $dayRule->getTarget() / 1000 / 60 : 0;
                    $targetSaldo = Saldo::createFromHoursAndMinutes(0, $target, false);
                    $dayParts = $curr->dayParts;
                    $daySaldo = array_reduce($dayParts, function (Saldo $prev, WorkingDayPart $curr) {
                        return Saldo::getSum($prev, $curr->getActualSaldo());
                    }, Saldo::createFromHoursAndMinutes());
                    $currentSaldo = Saldo::getSum($daySaldo, $targetSaldo);
                } elseif ($timeOff === 'gleitzeit') {
                    $target = $dayRule ? $dayRule->getTarget() / 1000 / 60 : 0;
                    $currentSaldo = Saldo::createFromHoursAndMinutes(0, $target, false);
                } elseif ($timeOff === 'zusatz') {
                    $dayParts = $curr->dayParts;
                    $currentSaldo = array_reduce($dayParts, function (Saldo $prev, WorkingDayPart $curr) {
                        return Saldo::getSum($prev, $curr->getActualSaldo());
                    }, Saldo::createFromHoursAndMinutes());
                } else {
                    $currentSaldo = Saldo::createFromHoursAndMinutes();
                }
                return Saldo::getSum($prev, $currentSaldo);
            }, Saldo::createFromHoursAndMinutes());
            $result = [
                'text' => 'Hallo Welt!',
                'db' => $workingMonth->getArrayCopy(),
                'saldo' =>  (string) $saldo,
            ];
            return $this->processResult($result, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }
}
