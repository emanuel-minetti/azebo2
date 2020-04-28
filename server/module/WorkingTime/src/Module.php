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

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Service\log\AzeboLog;
use WorkingTime\Model\WorkingDayTable;

class Module {
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\WorkingDayTable::class => function (ServiceManager $sm) {
                    $dbAdapter = $sm->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\WorkingDay([]));
                    $tableGateway = new TableGateway('working_day', $dbAdapter, null, $resultSetPrototype);
                    return new Model\WorkingDayTable($tableGateway);
                },
                AzeboLog::class => InvokableFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\WorkingTimeController::class => function (ServiceManager $sm) {
                    $logger = $sm->get(AzeboLog::class);
                    $table = $sm->get(WorkingDayTable::class);
                    return new Controller\WorkingTimeController($logger, $table);
                },
            ],
        ];
    }
}

