<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Holiday;

use Holiday\Controller\HolidayController;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Service\log\AzeboLog;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                AzeboLog::class => InvokableFactory::class,
            ]
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                HolidayController::class => function(ServiceManager $sm) {
                    return new HolidayController($sm->get(AzeboLog::class));
                }
            ]
        ];
    }

}
