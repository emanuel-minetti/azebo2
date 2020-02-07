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
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

use Service\AuthorizationService;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;

class WorkingRuleController extends AbstractActionController
{
    private $table;

    public function __construct(WorkingRuleTable $table)
    {
        $this->table = $table;
    }

    public function allAction()
    {
        if (AuthorizationService::authorize($this->request, $this->response, ['GET',])) {
            $userId = $this->request->getQuery()->user_id;
            $arrayOfWorkingRules = $this->table->getByUserId($userId);
            return $this->processResult($arrayOfWorkingRules, $userId);
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
            return $this->processResult($arrayOfWorkingRules, $userId);
        } else {
            return $this->response;
        }
    }

    private function processResult($result, $userId) {
        $arrayOfWorkingRuleArrays = [];
        foreach ($result as $workingRule) {
            /** @var WorkingRule $workingRule */
            $arrayOfWorkingRuleArrays[] = $workingRule->getArrayCopy();
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
                'working_rules' => $arrayOfWorkingRuleArrays,
            ],
        ]);
    }
}
