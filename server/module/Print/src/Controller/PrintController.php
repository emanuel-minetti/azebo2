<?php

namespace Print\Controller;

use Service\log\AzeboLog;

class PrintController extends \AzeboLib\ApiController {
    public function __construct(AzeboLog $logger) {
        parent::__construct($logger);
    }
    public function printAction() {
        $this->prepare();
        $result = [
            'text' => "Hallo from Print",
        ];
        return $this->processResult($result, 0);
    }

}