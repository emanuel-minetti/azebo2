<?php
/**
 * azebo2 is an application to print working time tables
 * Copyright (C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use WorkingRule\Model\WorkingRuleTable;

class WorkingRuleController extends AbstractActionController
{
    private $table;

    public function __construct(WorkingRuleTable $table)
    {
        $this->table = $table;
    }

    public function testAction()
    {
        $rule = $this->table->find(1);
        return new JsonModel([
            'success' => true,
            'data' => $rule->getArrayCopy(),
        ]);
    }


//
//    /** @noinspection PhpUnused */
//    public function monthAction()
//    {
//        $yearId = $this->params('year');
//        $monthId = $this->params('month');
//        $month = DateTime::createFromFormat(WorkingDay::DATE_FORMAT, "$yearId-$monthId-01");
//        $request = Request::fromString($this->request);
//        $response = Response::fromString($this->response);
//        if (AuthorizationService::authorize($request, $response, ['GET',])) {
//            $userId = $request->getQuery()->user_id;
//            $arrayOfWorkingDays = $this->table->getByUserIdAndMonth($userId, $month);
//            $arrayOfWorkingDayArrays = [];
//            foreach ($arrayOfWorkingDays as $workingDay) {
//                $arrayOfWorkingDayArrays[] = $workingDay->getArrayCopy();
//            }
//
//            // refresh jwt ...
//            $expire = time() + AuthorizationService::EXPIRE_TIME;
//            $jwt = AuthorizationService::getJwt($expire, $userId);
//            // ... and return response
//            return new JsonModel([
//                'success' => true,
//                'data' => [
//                    'jwt' => $jwt,
//                    'expire' => $expire,
//                    'working_days' => $arrayOfWorkingDayArrays,
//                ],
//            ]);
//        } else {
//            // `response` was set in the call to `AuthorizationService::authorize`
//            return $response;
//        }
//    }
}
