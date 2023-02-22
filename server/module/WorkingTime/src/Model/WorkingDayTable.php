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
use WorkingRule\Model\WorkingRuleTable;

class WorkingDayTable
{
    private TableGateway $tableGateway;
    private WorkingDayPartTable $dayPartTable;
    private WorkingRuleTable $rulesTable;

    public function __construct(TableGateway $tableGateway, WorkingDayPartTable $dayPartTable, WorkingRuleTable $rulesTable)
    {
        $this->tableGateway = $tableGateway;
        $this->dayPartTable = $dayPartTable;
        $this->rulesTable = $rulesTable;
    }

    public function find($id): ?WorkingDay {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        $day = $rowSet->current();
        $this->prepareDay($day);
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
            $this->prepareDay($row);
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
            foreach ($day->getDayParts() as $part) {
                $part->workingDayId = $dayId;
                $this->dayPartTable->upsert($part);
            }
        } else {
            $formerDay = $this->find($day->id);
            $copy = $day->getArrayCopy();
            unset($copy['day_parts']);
            $this->tableGateway->update($copy, ['id' => $day->id]);
            if ($day->getDayParts()) {
                if ($formerDay->getDayParts()) {
                    // upsert the new one and keep track
                    foreach ($day->getDayParts() as $part) {
                        $this->dayPartTable->upsert($part);
                        $formerDay->setDayParts(array_filter($formerDay->getDayParts(), function ($partBefore) use ($part){
                            return $partBefore->id !== $part->id;
                        }));
                    }
                    // delete remaining old ones
                    foreach ($formerDay->getDayParts() as $part) {
                        $this->dayPartTable->delete($part);
                    }
                } else {
                    foreach ($day->getDayParts() as $part) {
                        $this->dayPartTable->upsert($part);
                    }
                }
            } else {
                if ($formerDay->getDayParts()) {
                    foreach ($formerDay->getDayParts() as $part) {
                        $this->dayPartTable->delete($part);
                    }
                }
            }
        }
    }

    public function getByUserIdAndDay($userId, DateTime $date): WorkingDay | null {
        $rowSet = $this->tableGateway->select([
            'User_id' => $userId,
            'date' => $date->format(WorkingDay::DATE_FORMAT)
        ]);
        if ($rowSet->count() === 0) {
            return null;
        }
        $day = $rowSet->current();
        $this->prepareDay($day);
        return $day;
    }

    private function prepareDay(WorkingDay $day): void {
        $day->setDayParts($this->dayPartTable->getBayDayId($day->id));
        $rules = $this->rulesTable->getByUserIdAndMonth($day->userId, $day->date);
        foreach ($rules as $rule) {
            if ($rule->isValid($day->date)) {
                $day->setRule($rule);
                break;
            }
        }
    }

}
