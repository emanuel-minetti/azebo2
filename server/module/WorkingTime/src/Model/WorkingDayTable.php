<?php
/**
 *
 * azebo2 is an application to print working time tables
 * Copyright(C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version .
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE . See the
 * GNU General Public License for more details .
 *
 * You should have received a copy of the GNU General Public License
 * along with this program .  If not, see < https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright(c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Model;

use DateTime;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;

class WorkingDayTable
{
    private $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function find($id): WorkingDay {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        return $rowSet->current();
    }

    public function getByUserIdAndMonth($userId, DateTime $month) {
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
            $result[] = $row;
        }
        return $result;
    }
}
