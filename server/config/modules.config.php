<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Laminas\Log',
    'Laminas\Db',
    'Laminas\Router',
    'Laminas\Validator',
    'Application',
    'Login',
    'WorkingTime',
    'Holiday',
    'WorkingRule',
    'Carry',
];
