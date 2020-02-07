<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime;

use Laminas\Router\Http\Segment;

return array(
    'router' => [
        'routes' => [
            'working-time' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/working-time/:year/:month',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WorkingTimeController::class,
                        'action' => 'month',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
);
