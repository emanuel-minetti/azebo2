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

use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return array(
    'router' => [
        'routes' => [
            'holiday' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/holiday/:year',
                    'constraints' => [
                        'year' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\HolidayController::class,
                        'action' => 'get',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\HolidayController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
);
