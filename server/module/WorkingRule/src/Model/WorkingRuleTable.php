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

use DateInterval;
use DateTime;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Login\Model\User;

class WorkingRuleTable
{
    public const DATE_FORMAT = 'Y-m-d';
    private TableGateway $ruleGateway;
    private TableGateway $weekdayGateway;
    private Sql $sql;

    public function __construct(TableGateway $ruleGateway, TableGateway $weekdayGateway)
    {
        $this->ruleGateway = $ruleGateway;
        $this->weekdayGateway = $weekdayGateway;
        $this->sql = $ruleGateway->getSql();
    }

    public function find($id): WorkingRule
    {
        $rowSet = $this->ruleGateway->select(['id' => $id]);
        return $rowSet->current();
    }

    public function getByUserId($userId): array {
        $where = new Where();
        $where->equalTo('user_id', $userId);
        return $this->getResult($where);
    }

    public function getByUserIdAndMonth($userId, DateTime $month): array {
        $cloneOfMonth = clone $month;
        $first = $cloneOfMonth->modify('first day of this month');
        $cloneOfMonth = clone $month;
        $last = $cloneOfMonth->modify('last day of this month');
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
        return $this->getResult($where);
    }

    /**
     * @param WorkingRule $rule
     * @return WorkingRule|false
     */
    public function insert(WorkingRule $rule): WorkingRule|false {
        $selectRunning = $this->sql->select();
        //$selectRunning->where->isNull('valid_to')->and->equalTo('user_id', $rule->userId);
        $where = new Where();
        $where->isNull('valid_to')->and->equalTo('user_id', $rule->userId);
        $selectRunning->where($where);
        $running = $this->ruleGateway->selectWith($selectRunning);
        if ($running->current()) {
            $validFrom = clone $rule->validFrom;
            $this->ruleGateway->update([
                'valid_to' => $validFrom->sub(new DateInterval('P1D'))->format(self::DATE_FORMAT),
            ], [
                'id' => $running->current()['id'],
            ]);
        }
        $selectOverwrite = $this->sql->select();
        $overwriteWhere = new Where();
        $done = false;
        $overwriteWhere
            ->equalTo('user_id', $rule->userId)
            ->and
            ->equalTo('valid_from', $rule->validFrom->format(self::DATE_FORMAT));
        $selectOverwrite->where($overwriteWhere);
        $overwrite = $this->ruleGateway->selectWith($selectOverwrite);
        if ($overwrite->current()) {
            if ($overwrite->current()['id'] === $running->current()['id']) {
                $this->ruleGateway->update([
                    'valid_from' => $rule->validFrom->format(self::DATE_FORMAT),
                    'valid_to' => $rule->validTo?->format(self::DATE_FORMAT),
                    'percentage' => $rule->percentage,
                    'has_weekdays' => $rule->hasWeekdays,
                    'is_officer' => $rule->isOfficer ? 1 : 0,
                ], [
                    'id' => $running->current()['id'],
                ]);
                $ruleId = $running->current()['id'];
                $done = true;
            } else {
                return false;
            }
        }
        if (!$done) {
            $arrayCopy = $rule->getArrayCopy();
            $arrayCopy['has_weekdays'] = $rule->hasWeekdays;
            unset($arrayCopy['weekdays']);
            unset($arrayCopy['id']);
            unset($arrayCopy['target']);
            $this->ruleGateway->insert($arrayCopy);
            $ruleId = $this->ruleGateway->getLastInsertValue();
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $rule->id = $ruleId;
        $deleteWeekdays = new Delete('working_rule_weekday');
        $deleteWhere = new Where();
        $deleteWhere->equalTo('working_rule_id', $ruleId);
        $deleteWeekdays->where($deleteWhere);
        $this->weekdayGateway->deleteWith($deleteWeekdays);
        if ($rule->hasWeekdays) {
            $insertWeekdays = new Insert('working_rule_weekday');
            foreach ($rule->weekdays as $weekday) {
                $insertWeekdays->values([
                    'working_rule_id' => $ruleId,
                    'weekday' => $weekday,
                ]);
                $this->weekdayGateway->insertWith($insertWeekdays);
            }
        }
        return $rule;
    }

    public function insertNewUser(User $user): void {
        $rule = [
            'user_id' => $user->id,
            'valid_from' => '2023-1-1',
            'has_weekdays' => false,
            'percentage' => 100,
            'is_officer' => 0,
        ];
        $this->ruleGateway->insert($rule);
    }

    public function getWeekdays(WorkingRule $rule): array {
        if ($rule->hasWeekdays) {
            $weekdaySelect = new Select('working_rule_weekday');
            $weekdaySelect->columns(['weekday']);
            $weekdaySelect->where("working_rule_id = $rule->id");
            $result = [];
            $weekdayResultSet = $this->weekdayGateway->selectWith($weekdaySelect);
            foreach ($weekdayResultSet as $weekdayRow) {
                $result[] = $weekdayRow['weekday'];
            }
            return $result;
        } else {
            return [1, 2, 3, 4, 5,];
        }
    }

    /**
     * @param Where $where
     * @return array
     */
    private function getResult(Where $where): array {
        $select = $this->sql->select();
        $select->where($where);
        $select->order('valid_from ASC');
        $resultSet = $this->ruleGateway->selectWith($select);
        $result = [];
        foreach ($resultSet as $row) {
            $rule = new WorkingRule($row->getArrayCopy());
            $rule->weekdays = $this->getWeekdays($rule);
            $result[] = $rule;
        }
        return $result;
    }

}
