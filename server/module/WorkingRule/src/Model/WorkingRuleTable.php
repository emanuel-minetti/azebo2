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
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class WorkingRuleTable
{
    private $tableGateway;
    private $sql;

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

    public function getByUserId($userId) {
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

    public function getByUserIdAndMonth($userId, DateTime $month) {
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
            $result[] = $row;
        }
        return $result;
    }
}
