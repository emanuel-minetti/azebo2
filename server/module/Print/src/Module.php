<?php /** @noinspection PhpUnused */

namespace Print;

use Carry\Model\WorkingMonthTable;
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
                PrintController::class => function (ServiceManager $sm) {
                    $logger = $sm->get(AzeboLog::class);
                    $monthTable = $sm->get(WorkingMonthTable::class);
                    return new PrintController($logger, $monthTable);
                }
            ],
        ];
    }
}