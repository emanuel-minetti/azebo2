<?php /** @noinspection PhpUnused */

namespace Print\Controller;

use AzeboLib\ApiController;
use Laminas\View\Model\JsonModel;
use Service\log\AzeboLog;

class PrintController extends ApiController {
    public function __construct(AzeboLog $logger) {
        parent::__construct($logger);
    }
    public function printAction(): JsonModel {
        $this->prepare();
        $result = [
            'text' => "Hallo from Print",
        ];
        return $this->processResult($result, 0);
    }

}