<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Message;

use Laminas\ServiceManager\ServiceManager;
use Message\Controller\MessageController;
use Message\Model\Message;
use Service\log\AzeboLog;

class Module {
    public function getConfig() {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig(): array {
        return [
            'factories' => [
                MessageController::class => function (ServiceManager $container) {
                    return new MessageController(
                        $container->get(AzeboLog::class),
                        new Message()
                    );
                }
            ],
        ];
    }
}