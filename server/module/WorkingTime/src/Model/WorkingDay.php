<?php
/**
 * azebo2 is an application to print working-time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingTime\Model;

use ArrayObject;
use AzeboLib\Saldo;
use DateTime;
use WorkingRule\Model\WorkingRule;

class WorkingDay extends ArrayObject
{
    public const DATE_FORMAT = 'Y-m-d';

    /** @var int the primary key of `WorkingDay` */
    public int $id;
    /** @var int foreign key to `User.php` */
    public int $userId;
    /** @var DateTime the date of the working day */
    public DateTime $date;
    /** @var string an enumerated value */
    public string $timeOff;
    /** @var string a free text field */
    public string $comment;
    private array | null $dayParts = null;
    private WorkingRule | null $rule;
    // TODO make private! (Should be set by table and read by controllers!)
    public Saldo | null $saldo;

    public function exchangeArray($array): array {
        $this->id = (int)$array['id'] ?? 0;
        $this->userId = (int)$array['user_id'] ?? 0;
        $this->date = !empty($array['date']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['date']) : null;
        $this->timeOff = $array['time_off'] ?? "";
        $this->comment = $array['comment'] ?? "";
        return array($this);
    }

    public function getArrayCopy(): array
    {
        return [
            'id' => $this->id ?? null,
            'user_id' => $this->userId,
            'date' => $this->date->format(self::DATE_FORMAT),
            'time_off' => $this->timeOff == "" ? null : $this->timeOff,
            'comment' => $this->comment == "" ? null: $this->comment,
            'day_parts' => $this->dayParts,
        ];
    }

    /**
     * @return array | null
     */
    public function getDayParts(): array | null {
        return $this->dayParts;
    }

    /**
     * @param array $dayParts
     */
    public function setDayParts(array $dayParts): void {
        $this->dayParts = $dayParts;
    }

    public function addDayPart(WorkingDayPart $part): void {
        if ($this->dayParts === null) {
            $this->dayParts = [];
        }
        $this->dayParts[] = $part;
    }

    /**
     * @return WorkingRule|null
     */
    public function getRule(): ?WorkingRule {
        return $this->rule;
    }

    /**
     * @param WorkingRule|null $rule
     */
    public function setRule(?WorkingRule $rule): void {
        $this->rule = $rule;
    }


    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }
}
