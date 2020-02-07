<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule;

use Laminas\Router\Http\Segment;
use WorkingRule\Controller\WorkingRuleController;

return array(
    'router' => [
        'routes' => [
            'working-rules' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/working-rule',
                    'defaults' => [
                        'controller' => WorkingRuleController::class,
                        'action' => 'all',
                    ],
                ],
            ],
            'working-rule-by-month' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/working-rule/:year/:month',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => WorkingRuleController::class,
                        'action' => 'byMonth',
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
