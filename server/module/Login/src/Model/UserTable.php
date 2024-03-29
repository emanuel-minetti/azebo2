<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */


namespace Login\Model;


use RuntimeException;
use Laminas\Db\TableGateway\TableGateway;

class UserTable
{
    private TableGateway $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function find(int $id): User | null {
        $rowSet = $this->tableGateway->select(['id' => $id]);
        return $rowSet->current();
    }
    /**
     * @param String $username
     * @return User
     */
    public function getUserByUsername(string $username): User
    {
        $rowset = $this->tableGateway->select(['username' => $username]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row for username %s',
                $username
            ));
        }
        return $row;
    }

    public function insert(User $user): void
    {
        $this->tableGateway->insert($user->getArrayCopy());
    }
}
