<?php

namespace Print;

use Laminas\ServiceManager\ServiceManager;
use Print\Controller\PrintController;
use Service\log\AzeboLog;

class Module {

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig(): array {
        return [
            'factories' => [
                PrintController::class => function (ServiceManager $container) {
                    return new PrintController(
                        $container->get(AzeboLog::class)
                    );
                }
            ],
        ];
    }
}