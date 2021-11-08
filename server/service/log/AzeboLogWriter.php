<?php

namespace Service\log;

use Laminas\Config\Factory;
use Laminas\Log\Writer\Stream;

class AzeboLogWriter extends Stream
{
    public function __construct()
    {
        $config = Factory::fromFile(__DIR__ . '/../../config/autoload/local.php');
        $path = $config['log']['pathToLogFile'];
        parent::__construct($path);
    }
}