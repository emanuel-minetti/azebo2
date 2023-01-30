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

class WorkingDayTable
{
    private TableGateway $tableGateway;
    private WorkingDayPartTable $dayPartTable;

    public function __construct(TableGateway $tableGateway, WorkingDayPartTable $dayPartTable)
    {
        $this->tableGateway = $tableGateway;
        $this->dayPartTable = $dayPartTable;
    }

    public function find($id): ?WorkingDay {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        $day = $rowSet->current();
        $day->dayParts = $this->dayPartTable->getBayDayId($id);
        return $day;
    }

    public function getByUserIdAndMonth($userId, DateTime $month): array
    {
        $cloneOfMonth = clone $month;
        $first = $cloneOfMonth->modify('first day of this month');
        $cloneOfMonth = clone $month;
        $last = $cloneOfMonth->modify('last day of this month');
        $select = new Select('working_day');
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->greaterThanOrEqualTo('date', $first->format(WorkingDay::DATE_FORMAT));
        $where->lessThanOrEqualTo('date', $last->format(WorkingDay::DATE_FORMAT));
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $row->dayParts = $this->dayPartTable->getBayDayId($row->id);
            $result[] = $row;
        }
        return $result;
    }

    public function upsert(WorkingDay $day): void {
        if ($day->id == 0) {
            unset($day->id);
            //$dayParts = $day->dayParts;
            $copy = $day->getArrayCopy();
            unset($copy['day_parts']);
            $this->tableGateway->insert($copy);
            $dayId = $this->tableGateway->getLastInsertValue();
            $day->id = $dayId;
            /** @var WorkingDayPart $part */
            foreach ($day->dayParts as $part) {
                $part->workingDayId = $dayId;
                $this->dayPartTable->upsert($part);
            }
        } else {
            $formerDay = $this->find($day->id);
            $copy = $day->getArrayCopy();
            unset($copy['day_parts']);
            $this->tableGateway->update($copy, ['id' => $day->id]);
            if (isset($day->dayParts)) {
                if (isset($formerDay->dayParts)) {
                    // upsert the new one and keep track
                    foreach ($day->dayParts as $part) {
                        $this->dayPartTable->upsert($part);
                        $formerDay->dayParts = array_filter($formerDay->dayParts, function ($partBefore) use ($part){
                            return $partBefore->id !== $part->id;
                        });
                    }
                    // delete remaining old ones
                    foreach ($formerDay->dayParts as $part) {
                        $this->dayPartTable->delete($part);
                    }
                } else {
                    foreach ($day->dayParts as $part) {
                        $this->dayPartTable->upsert($part);
                    }
                }
            } else {
                if (isset($formerDay->dayParts)) {
                    foreach ($formerDay->dayParts as $part) {
                        $this->dayPartTable->delete($part);
                    }
                }
            }
        }
    }
}
