<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Model;

use DateTime;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class WorkingDayPartTable
{
    private TableGateway $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function find($id): ?WorkingDay {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        return $rowSet->current();
    }

    public function getBayDayId(int $dayId): array {
        $result = [];
        $rowSet = $this->tableGateway->select(['working_day_id' => $dayId]);
        foreach ($rowSet as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function upsert(WorkingDayPart $part): void {
        if ($part->id === 0) {
            $copy = $part->getArrayCopy();
            unset($copy->id);
            $this->tableGateway->insert($copy);
            $part->id = $this->tableGateway->getLastInsertValue();
        } else {
            // TODO implement
        }
    }

    public function delete(WorkingDayPart $part):void {
        $this->tableGateway->delete(['id' => $part->id]);
    }


}
