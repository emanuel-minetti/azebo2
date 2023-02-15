<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Print;

use Laminas\Router\Http\Segment;
use Print\Controller\PrintController;

return array(
    'router' => [
        'routes' => [
            'print' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/print/:year/:month',
                    'constraints' => [
                        'year' => '[0-9]+',
                        'month' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => PrintController::class,
                        'action' => 'print',
                    ],
                ],
            ],
        ],
    ],
);
