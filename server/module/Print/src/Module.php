<?php /** @noinspection PhpUnused */

namespace Print;

use Carry\Model\WorkingMonthTable;
use Laminas\ServiceManager\ServiceManager;
use Login\Model\UserTable;
use Print\Controller\PrintController;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRuleTable;

class Module {

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig(): array {
        return [
            'factories' => [
                PrintController::class => function (ServiceManager $sm) {
                    $logger = $sm->get(AzeboLog::class);
                    $monthTable = $sm->get(WorkingMonthTable::class);
                    $userTable = $sm->get(UserTable::class);
                    $ruleTable = $sm->get(WorkingRuleTable::class);
                    return new PrintController($logger, $monthTable, $userTable, $ruleTable);
                }
            ],
        ];
    }
}