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
            $result[] = $row;
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
            $result[] = $rule;
        }
        return $result;
    }

    /**
     * @param User $user
     * @return void
     */
    public function insert(User $user): void
    {
        $firstOfMonth = new DateTime('first day of this month');
        $ruleData = [
            'user_id' => $user->id,
            'calendar_week' => 'all',
            'flex_time_begin' => '06:30:00',
            'flex_time_end' => '20:00:00',
            'core_time_begin' => '09:30:00',
            'core_time_end' => '14:30:00',
            'target' => '07:52:00',
            'valid_from' => $firstOfMonth->format(WorkingRule::DATE_FORMAT),
        ];
        $rule = new WorkingRule();
        for ($i = 1; $i <= 5; $i++) {
            $ruleData['weekday'] = $i;
            $rule->exchangeArray($ruleData);
            $this->tableGateway->insert($rule->getArrayCopy());
        }
    }
}
