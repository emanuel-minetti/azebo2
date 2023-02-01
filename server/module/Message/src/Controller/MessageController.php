<?php

namespace Message\Controller;

use AzeboLib\ApiController;
use Laminas\View\Model\JsonModel;
use Message\Model\Message;
use Service\log\AzeboLog;

class MessageController extends ApiController {
    private Message $message;
    public function __construct(AzeboLog $logger, Message $message) {
        $this->message = $message;
        parent::__construct($logger);
    }

    public function indexAction(): JsonModel {
        $this->prepare();
        return $this->processResult($this->message->getArrayCopy(), 0);
    }
}