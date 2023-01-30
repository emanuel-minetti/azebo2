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
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;

class WorkingMonthTable
{

    private TableGateway $tableGateway;
    private Sql $sql;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->sql = $this->tableGateway->getSql();
    }

    /**
     * @param $userId
     * @param DateTime $month
     * @param bool $includeEarlier whether to include earlier months into array
     * @return WorkingMonth[]
     * @noinspection PhpParamsInspection
     */
    public function getByUserIdAndMonth($userId, DateTime $month, bool $includeEarlier = true): array
    {
        $select = $this->sql->select();
        $where = new Where();
        if ($includeEarlier) {
            $where->equalTo('user_id', $userId)
                ->and
                ->equalTo('carried', 0)
                ->and
                ->lessThanOrEqualTo(new Literal('MONTH(month)'), $month->format('n'));
        } else {
            $where->equalTo('user_id', $userId)
                ->and
                ->equalTo('carried', 0)
                ->and
                ->equalTo(new Literal('MONTH(month)'), $month->format('n'));
        }
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $result[] = $row;
        }
        return $result;
    }
}