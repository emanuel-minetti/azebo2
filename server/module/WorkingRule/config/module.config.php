<?php
/**
 * azebo2 is an application to print working time tables
 * Copyright (C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule;

use Laminas\Router\Http\Segment;
use WorkingRule\Controller\WorkingRuleController;

return array(
    'router' => [
        'routes' => [
            'working-rule' => [
                'type' => Segment::class,
                'options' => [
                    // TODO review route
                    'route' => '/api/working-rule[/:year/:month]',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => WorkingRuleController::class,
                        'action' => 'test',
                    ],
                ],
            ],
        ],
    ],

//    'controllers' => [
//        'factories' => [
//            WorkingRuleController::class => InvokableFactory::class,
//        ],
//    ],

    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
);
