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

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\ServiceManager;

use Carry\Model\WorkingMonth;
use Carry\Model\WorkingMonthTable;
use Carry\Controller\CarryController;

class Module {
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                WorkingMonthTable::class => function (ServiceManager $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new WorkingMonth());
                    $tableGateway = new TableGateway('working_month', $dbAdapter, null, $resultSetPrototype);
                    return new WorkingMonthTable($tableGateway);
                }
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                CarryController::class => function (ServiceManager $container) {
                    return new CarryController($container->get(WorkingMonthTable::class));
                }
            ],
        ];
    }
}
