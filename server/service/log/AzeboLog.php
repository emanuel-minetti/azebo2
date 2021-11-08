<?php

namespace Service\log;

use Laminas\Log\Logger;

class AzeboLog extends Logger
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->addWriter(new AzeboLogWriter());
        Logger::registerErrorHandler($this);
        Logger::registerExceptionHandler($this);
    }
}
