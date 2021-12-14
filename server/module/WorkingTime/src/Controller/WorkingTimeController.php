<?php /** @noinspection PhpUnused */

/**
 * azebo2 is an application to print working time tables
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
use Service\AuthorizationService;
use Service\log\AzeboLog;
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
            $id = $post->_id;
            $oneHour = new DateInterval('PT1H');
            if ($id != 0) {
                $day = $this->table->find($id);
            } else {
                $day = new WorkingDay();
                $day->date = DateTime::createFromFormat("Y-m-d\TH:i:s+", $post->_date);
                $day->date->add($oneHour);
                $day->userId = $userId; $this->prepare();
            $post = json_decode($this->httpRequest->getContent());
                $day->id = 0;
            }
            if (isset($post->_begin)) {
                $day->begin = DateTime::createFromFormat("Y-m-d\TH:i:s+", $post->_begin);
                $day->begin->add($oneHour);
            }
            if (isset($post->_end)) {
                $day->end = DateTime::createFromFormat("Y-m-d\TH:i:s+", $post->_end);
                $day->end->add($oneHour);
            }
            $day->timeOff = $post->_timeOff ?? "";
            $day->comment = $post->_comment ?? "";
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
