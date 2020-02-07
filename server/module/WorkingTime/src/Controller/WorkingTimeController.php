<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Controller;

use DateTime;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

use Service\AuthorizationService;
use WorkingTime\Model\WorkingDay;
use WorkingTime\Model\WorkingDayTable;

class WorkingTimeController extends AbstractActionController
{
    private $table;

    public function __construct(WorkingDayTable $table)
    {
        $this->table = $table;
    }

    /** @noinspection PhpUnused */
    public function monthAction()
    {
        $yearId = $this->params('year');
        $monthId = $this->params('month');
        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearId-$monthId-01");
        $request = Request::fromString($this->request);
        $response = Response::fromString($this->response);
        if (AuthorizationService::authorize($request, $response, ['GET',])) {
            $userId = $request->getQuery()->user_id;
            $arrayOfWorkingDays = $this->table->getByUserIdAndMonth($userId, $month);
            $arrayOfWorkingDayArrays = [];
            foreach ($arrayOfWorkingDays as $workingDay) {
                $arrayOfWorkingDayArrays[] = $workingDay->getArrayCopy();
            }

            // refresh jwt ...
            $expire = time() + AuthorizationService::EXPIRE_TIME;
            $jwt = AuthorizationService::getJwt($expire, $userId);
            // ... and return response
            return new JsonModel([
                'success' => true,
                'data' => [
                    'jwt' => $jwt,
                    'expire' => $expire,
                    'working_days' => $arrayOfWorkingDayArrays,
                ],
            ]);
        } else {
            // `response` was set in the call to `AuthorizationService::authorize`
            return $response;
        }
    }
}
