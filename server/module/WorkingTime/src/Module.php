<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime;

use Carry\Model\CarryTable;
use Carry\Model\WorkingMonthTable;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRuleTable;
use WorkingTime\Model\WorkingDayPartTable;
use WorkingTime\Model\WorkingDayTable;

class Module {
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig(): array {
        return [
            'factories' => [
                Model\WorkingDayPartTable::class => function (ServiceManager $sm) {
                    $dbAdapter = $sm->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\WorkingDayPart([]));
                    $tableGateway = new TableGateway('working_day_part', $dbAdapter, null, $resultSetPrototype);
                    return new Model\WorkingDayPartTable($tableGateway);
                },
                Model\WorkingDayTable::class => function (ServiceManager $sm) {
                    $dbAdapter = $sm->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\WorkingDay([]));
                    $tableGateway = new TableGateway('working_day', $dbAdapter, null, $resultSetPrototype);
                    $dayPartTable = $sm->get(WorkingDayPartTable::class);
                    return new Model\WorkingDayTable($tableGateway, $dayPartTable);
                },
                AzeboLog::class => InvokableFactory::class,
            ],
        ];
    }

    public function getControllerConfig(): array {
        return [
            'factories' => [
                Controller\WorkingTimeController::class => function (ServiceManager $sm) {
                    $logger = $sm->get(AzeboLog::class);
                    $dayTable = $sm->get(WorkingDayTable::class);
                    $monthTable = $sm->get(WorkingMonthTable::class);
                    $ruleTable = $sm->get(WorkingRuleTable::class);
                    $carryTable = $sm->get(CarryTable::class);
                    return new Controller\WorkingTimeController($logger, $dayTable, $monthTable,$ruleTable, $carryTable);
                },
            ],
        ];
    }
}

