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
use Exception;
use Laminas\Db\Sql\Literal;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Login\Model\User;

class CarryTable
{
    private $tableGateway;
    private $sql;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->sql = $this->tableGateway->getSql();
    }

    /**
     * @param int $userId the User id
     * @param DateTime $year the year
     * @return Carry
     */
    public function getByUserIdAndYear($userId, DateTime $year) {
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('user_id', $userId)
            ->and
            ->equalTo(new Literal('YEAR(year)'), $year->format('Y'));
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current();
    }

    public function insert(User $user)
    {
        $data = [];
        $data['user_id'] = $user->id;
        $carry = new Carry();
        $carry->exchangeArray($data);
        $carry->year = new DateTime();
        $this->tableGateway->insert($carry->getArrayCopy());
    }

    public function update(Carry $carry) {
        $where = new Where();
        $where->equalTo('id', $carry->id);
        $this->tableGateway->update($carry->getArrayCopy(), $where);
    }

    /**
     * Returns the carry over for given user and the current year
     *
     * @param $userId
     * @return Carry
     */
    public function getByUserId($userId)
    {
        $year = null;
        try {
            $year = new DateTime();
        } catch (Exception $e) {
        }
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('user_id', $userId)
            ->and
            ->equalTo(new Literal('YEAR(year)'), $year->format('Y'));
        $select->where($where);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current();
    }

    /**
     * Finds a `Carry` by its `id`.
     *
     * @param int $id
     * @return Carry
     */
    public function find(int $id)
    {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        return $rowSet->current();
    }

}