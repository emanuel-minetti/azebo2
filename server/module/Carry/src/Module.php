<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Carry;

use Carry\Controller\CarryController;
use Carry\Model\Carry;
use Carry\Model\CarryTable;
use Carry\Model\WorkingMonth;
use Carry\Model\WorkingMonthTable;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Service\log\AzeboLog;
use WorkingRule\Model\WorkingRule;
use WorkingRule\Model\WorkingRuleTable;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig(): array {
        return [
            'factories' => [
                WorkingMonthTable::class => function (ServiceManager $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new WorkingMonth());
                    $tableGateway = new TableGateway('working_month', $dbAdapter, null, $resultSetPrototype);
                    return new WorkingMonthTable($tableGateway);
                },
                CarryTable::class => function (ServiceManager $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Carry());
                    $tableGateway = new TableGateway('carry', $dbAdapter, null, $resultSetPrototype);
                    return new CarryTable($tableGateway);
                },
                WorkingRuleTable::class => function(ServiceManager $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new WorkingRule());
                    $ruleGateway = new TableGateway('working_rule', $dbAdapter, null, $resultSetPrototype);
                    $weekdayGateway = new TableGateway('working_rule_weekday', $dbAdapter);
                    return new WorkingRuleTable($ruleGateway, $weekdayGateway);
                },
                AzeboLog::class => InvokableFactory::class,
            ],
        ];
    }

    public function getControllerConfig(): array {
        return [
            'factories' => [
                CarryController::class => function (ServiceManager $container) {
                    return new CarryController(
                        $container->get(AzeboLog::class),
                        $container->get(WorkingMonthTable::class),
                        $container->get(CarryTable::class),
                        $container->get(WorkingRuleTable::class)
                    );
                }
            ],
        ];
    }
}
