<?php
/**
 *
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule\Model;

use DateTime;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Login\Model\User;

class WorkingRuleTable
{
    private TableGateway $tableGateway;
    private Sql $sql;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->sql = $tableGateway->getSql();
    }

    public function find($id): WorkingRule
    {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        return $rowSet->current();
    }

    public function getByUserId($userId): array {
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $rule = new WorkingRule($row->getArrayCopy());
            $rule->weekdays = $this->getWeekdays($rule);
            $result[] = $rule;
        }
        return $result;
    }

    public function getByUserIdAndMonth($userId, DateTime $month): array {
        $cloneOfMonth = clone $month;
        $first = $cloneOfMonth->modify('first day of this month');
        $cloneOfMonth = clone $month;
        $last = $cloneOfMonth->modify('last day of this month');
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('user_id', $userId)
            ->and
            ->nest()
            ->isNull('valid_to')
            ->or
            ->greaterThanOrEqualTo('valid_to', $first->format(WorkingRule::DATE_FORMAT))
            ->unnest()
            ->and
            ->lessThanOrEqualTo('valid_from', $last->format(WorkingRule::DATE_FORMAT));
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $rule = new WorkingRule($row->getArrayCopy());
            $rule->weekdays = $this->getWeekdays($rule);
            $result[] = $rule;
        }
        return $result;
    }

    /**
     * @param User $user
     * @return void
     */
    public function insert(WorkingRule $rule): void
    {
            $arrayCopy = $rule->getArrayCopy();
            $arrayCopy['has_weekdays'] = $rule->hasWeekdays;
            unset($arrayCopy['weekdays']);
            unset($arrayCopy['id']);
            unset($arrayCopy['target']);
            $this->tableGateway->insert($arrayCopy);
    }

    public function insertNewUser(User $user): void {
        $rule = [
            'user_id' => $user->id,
            'valid_from' => '2023-1-1',
            'has_weekdays' => false,
            'percentage' => 100,
        ];
        $this->tableGateway->insert($rule);
    }

    public function getWeekdays(WorkingRule $rule): array {
        if ($rule->hasWeekdays) {
            $weekdaySelect = new Select('working_rule_weekday');
            $weekdaySelect->columns(['weekday']);
            $weekdaySelect->where("working_rule_id = {$rule->id}");
            $result = [];
            $weekdayResultSet = $this->tableGateway->selectWith($weekdaySelect);
            foreach ($weekdayResultSet as $weekdayRow) {
                $result[] = $weekdayRow['weekday'];
            }
            return $result;
        } else {
            return [1, 2, 3, 4, 5,];
        }
    }

}
