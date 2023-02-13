<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Holiday\Controller;

use Exception;

use Laminas\Http\Request;
use Laminas\Http\Response;

use AzeboLib\ApiController;
use Laminas\View\Model\JsonModel;
use Service\HolidayService;
use Service\log\AzeboLog;
use Service\AuthorizationService;

class HolidayController extends ApiController
{
    public function __construct(AzeboLog $logger)
    {
        parent::__construct($logger);
    }

    /** @noinspection PhpUnused */
    public function getAction(): JsonModel|Response {
        $year = $this->params('year');
        $request = Request::fromString($this->request);
        $response = Response::fromString($this->response);
        if (AuthorizationService::authorize($request, $response)) {
            try {
                //$holidays = $this->getHolidays($year);
                $holidays = HolidayService::getHolidays($year);
            } catch (Exception $e) {
                $response->setStatusCode(500);
                $response->setContent($e->getMessage());
                return $response;
            }
            $userId = $request->getQuery()->user_id;
            return $this->processResult($holidays, $userId);
        } else {
            // `response` was set in the call to `AuthorizationService::authorize`
            return $response;
        }
    }
}
