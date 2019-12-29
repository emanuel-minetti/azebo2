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

namespace WorkingTime\Controller;

use Service\AuthorizationService;
use WorkingTime\Model\WorkingDayTable;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class WorkingTimeController extends AbstractActionController
{
    private $table;

    public function __construct(WorkingDayTable $table)
    {
        $this->table = $table;
    }

    public function monthAction()
    {
        $request = Request::fromString($this->request);
        $response = Response::fromString($this->response);
        if (AuthorizationService::authorize(
            $request,
            $response,
            ['GET',])) {


            // refresh jwt
            $userId = $this->request->getQuery()->user_id;
            $expire = time() + AuthorizationService::EXPIRE_TIME;
            $jwt = AuthorizationService::getJwt($expire, $userId);
            return new JsonModel([
                'success' => true,
                'jwt' => $jwt,
                'expire' => $expire,
            ]);
        } else {
            return $response;
        }
    }
}
