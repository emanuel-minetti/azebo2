<?php


namespace Service\log;


use Laminas\Log\Writer\Stream;

class AzeboLogWriter extends Stream
{
    public function __construct()
    {
        //TODO make file name configurable
        parent::__construct('../server/data/log/azebo.log');
    }
}