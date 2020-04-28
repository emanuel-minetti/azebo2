<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule\Controller;

use DateTime;

use AzeboLib\ApiController;
use Service\AuthorizationService;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;

class WorkingRuleController extends ApiController
{
    private $table;

    public function __construct(AzeboLog $logger, WorkingRuleTable $table)
    {
        parent::__construct($logger);
        $this->table = $table;
    }

    public function allAction()
    {
        if (AuthorizationService::authorize($this->request, $this->response, ['GET',])) {
            $userId = $this->request->getQuery()->user_id;
            $arrayOfWorkingRules = $this->table->getByUserId($userId);
            $resultArray = [];
            foreach ($arrayOfWorkingRules as $element) {
                $resultArray[] = $element->getArrayCopy();
            }
            return $this->processResult($resultArray, $userId);
        } else {
            // `response` was set in the call to `AuthorizationService::authorize`
            return $this->response;
        }
    }

    public function byMonthAction()
    {
        if (AuthorizationService::authorize($this->request, $this->response, ['GET',])) {
            $userId = $this->request->getQuery()->user_id;
            $yearId = $this->params('year');
            $monthId = $this->params('month');
            $month = DateTime::createFromFormat(WorkingRule::DATE_FORMAT, "$yearId-$monthId-01");
            $arrayOfWorkingRules = $this->table->getByUserIdAndMonth($userId, $month);
            $resultArray = [];
            foreach ($arrayOfWorkingRules as $element) {
                $resultArray[] = $element->getArrayCopy();
            }
            return $this->processResult($resultArray, $userId);
        } else {
            return $this->response;
        }
    }
}
