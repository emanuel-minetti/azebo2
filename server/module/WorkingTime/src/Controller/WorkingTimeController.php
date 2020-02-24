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
use DateTime;
use Laminas\View\Model\JsonModel;
use Service\AuthorizationService;
use WorkingTime\Model\WorkingDay;
use WorkingTime\Model\WorkingDayTable;

class WorkingTimeController extends ApiController
{
    private $table;

    public function __construct(WorkingDayTable $table)
    {
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
        $date = $post->_date;
        //$day = $this->table->find($post)
        return new JsonModel([
            'text' => $post,
            'date' => $date,
        ]);
    }
}
