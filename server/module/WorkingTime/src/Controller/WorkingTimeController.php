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
use DateInterval;
use DateTime;
use Exception;
use Laminas\Config\Factory;
use Laminas\Validator\StringLength;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use Validation\BeginBeforeEndValidator;
use Validation\TimeOffValidator;
use WorkingTime\Model\WorkingDay;
use WorkingTime\Model\WorkingDayTable;

class WorkingTimeController extends ApiController
{
    private WorkingDayTable $table;

    public function __construct(AzeboLog $logger, WorkingDayTable $table)
    {
        parent::__construct($logger);
        $this->table = $table;
    }

    public function monthAction()
    {
        $this->prepare();
        $yearParam = $this->params('year');
        $monthParam = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearParam-$monthParam-01");
        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ['GET',])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            $arrayOfWorkingDays = $this->table->getByUserIdAndMonth($userId, $month);
            $resultArray = [];
            foreach ($arrayOfWorkingDays as $element) {
                $resultArray[] = $element->getArrayCopy();
            }
            return $this->processResult($resultArray, $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }

    public function setDayAction()
    {
        $this->prepare();
        $post = json_decode($this->httpRequest->getContent());

        if (AuthorizationService::authorize($this->httpRequest, $this->httpResponse, ['POST'])) {
            $userId = $this->httpRequest->getQuery()->user_id;
            if (!isset($post->_id) || !is_numeric($post->_id)) return $this->invalidRequest;
            $id = $post->_id;
            if ($id != 0) {
                $day = $this->table->find($id);
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
            if (isset($post->_begin)) {
                $begin = DateTime::createFromFormat("H:i:s+", $post->_begin);
                if (!$begin) return $this->invalidRequest;
                $day->begin = $begin;
            } else {
                $day->begin = null;
            }
            if (isset($post->_end)) {
                $end = DateTime::createFromFormat("H:i:s+", $post->_end);
                if (!$end) return $this->invalidRequest;
                $day->end = $end;
            } else {
                $day->end = null;
            }
            if (isset($post->_begin) && isset($post->_end)) {
                // validate
                $bbeValidator = new BeginBeforeEndValidator();
                $value = [
                    'begin' => $day->begin,
                    'end' => $day->end
                ];
                if (!$bbeValidator->isValid($value)) return $this->invalidRequest;
                $config = Factory::fromFile('./../server/config/times.config.php', true);
                try {
                    $breakRequiredFrom = new DateInterval('PT' . $config->get('breakRequiredFrom') . 'H');
                    $longBreakRequiredFrom = new DateInterval('PT' . $config->get('longBreakRequiredFrom') . 'H');
                    $totalTime = $day->end->diff($day->begin, true);
                    $now = new DateTime();
                    if ((clone $now)->add($totalTime) > (clone $now)->add($longBreakRequiredFrom)) {
                        $day->break = new DateInterval("PT" . $config->get('longBreakDuration') . "M");
                    } elseif ((clone $now)->add($totalTime) > (clone $now)->add($breakRequiredFrom)) {
                        $day->break = new DateInterval("PT" . $config->get('breakDuration') . "M");
                    } else {
                        $day->break = new DateInterval("PT0M");
                    }
                } catch (Exception $ignored) {
                }
            }
            if (isset($post->_timeOff)) {
                $toValidator = new TimeOffValidator();
                $value = [
                    'begin' => $day->begin,
                    'end' => $day->end,
                    'timeOff' => $post->_timeOff,
                ];
                if (!$toValidator->isValid($value)) return $this->invalidRequest;
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
            $day->mobile_working = $post->_mobile_working ?? false;
            $day->afternoon = $post->_afternoon ?? false;
            $this->table->upsert($day);
            return $this->processResult($day->getArrayCopy(), $userId);
        } else {
            // `httpResponse` was set in the call to `AuthorizationService::authorize`
            return $this->httpResponse;
        }
    }
}
