<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Carry\Model;

use DateTime;
use Laminas\Db\Sql\Literal;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class CarryTable
{
    private $tableGateway;
    private $sql;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->sql = $this->tableGateway->getSql();
    }

    public function getByUserIdAndYear($userId, DateTime $year) {
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('user_id', $userId)
            ->and
            ->equalTo(new Literal('YEAR(year)'), $year->format('Y'));
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $result[] = $row;
        }
        return $result;
    }

}