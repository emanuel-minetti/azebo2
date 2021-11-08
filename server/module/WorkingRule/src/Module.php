<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

use Service\log\AzeboLog;
use WorkingRule\Controller\WorkingRuleController;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;

class Module {
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                WorkingRuleTable::class => function (ServiceManager $sm) {
                    $dbAdapter = $sm->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new WorkingRule([]));
                    $tableGateway = new TableGateway('working_rule', $dbAdapter, null, $resultSetPrototype);
                    return new WorkingRuleTable($tableGateway);
                },
                AzeboLog::class => InvokableFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                WorkingRuleController::class => function (ServiceManager $sm) {
                    $logger = $sm->get(AzeboLog::class);
                    $table = $sm->get(WorkingRuleTable::class);
                    return new WorkingRuleController($logger, $table);
                }
            ],
        ];
    }
}

