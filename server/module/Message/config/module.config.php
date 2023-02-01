<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Message;

use Laminas\Router\Http\Literal;
use Message\Controller\MessageController;

return array(
    'router' => [
        'routes' => [
            'message' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api/message',
                    'defaults' => [
                        'controller' => MessageController::class,
                        'action' => 'index',
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
