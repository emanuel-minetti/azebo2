<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Carry\Controller;

use AzeboLib\ApiController;
use Laminas\View\Model\JsonModel;

class CarryController extends ApiController
{
//    private $table;

//    public function __construct(WorkingDayTable $table)
//    {
//        $this->table = $table;
//    }

    /** @noinspection PhpUnused */
    public function carryAction() {
        return new JsonModel([
            'text' => "Hallo"
        ]);
    }
}
