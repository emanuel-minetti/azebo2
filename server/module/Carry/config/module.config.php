<?php
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
use Laminas\Router\Http\Method;
use Laminas\Router\Http\Segment;

return array(
    'router' => [
        'routes' => [
            'carry-result' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/carry-result/:year/:month',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => CarryController::class,
                        'action' => 'carryResult',
                    ],
                ],
            ],
            'carry' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/carry',
                    'defaults' => [
                        'controller' => CarryController::class,
                    ],
                ],
                'child_routes' => [
                    'get' => [
                        'type' => Method::class,
                        'options' => [
                            'verb' => 'get',
                            'defaults' => [
                                'action' => 'carry',
                            ]
                        ],
                    ],
                    'post' => [
                        'type' => Method::class,
                        'options' => [
                            'verb' => 'post',
                            'defaults' => [
                                'action' => 'setCarry',
                            ]
                        ],
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
