<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule\Model;

use ArrayObject;
use DateTime;
use Laminas\Config\Config;
use Laminas\Config\Factory;
use ReturnTypeWillChange;

class WorkingRule extends ArrayObject
{
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @var int the primary key of `WorkingRule`
     */
    public int $id;

    /**
     * @var int foreign key to `User
     */
    public int $userId;

    /**
     * @var DateTime The starting date for this rule.
     */
    public DateTime $validFrom;

    /**
     * @var DateTime|null The end date (if any) for this rule.
     */
    public DateTime|null $validTo;

    /**
     * @var bool Whether this rule has weekdays.
     */
    public bool $hasWeekdays;

    /**
     * @var int if "Teilzeit" the percentage, else 100.
     */
    public int $percentage;

    public bool $isOfficer;

    public DateTime|null $timestamp;

    public array $weekdays = [];

    private Config $config;

    public function __construct(array $array = []) {
        parent::__construct();
        if (sizeof($array) > 0) {
            $this->exchangeArray($array);
        }
    }

    #[ReturnTypeWillChange] public function exchangeArray($array)
    {
        $this->id = $array['id'] ?? 0;
        $this->userId = $array['user_id'] ?? 0;
        $this->validFrom = !empty($array['valid_from']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['valid_from']) : null;
        $this->validTo = !empty($array['valid_to']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['valid_to']) : null;
        $this->hasWeekdays = $array['has_weekdays'];
        $this->percentage = $array['percentage'];
        $this->timestamp = !empty($array['timestamp']) ?
            DateTime::createFromFormat(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, $array['timestamp']) : null;
        $this->weekdays = $array['weekdays'] ?? [];
        $this->isOfficer = (bool)$array['is_officer'];
    }

    #[ReturnTypeWillChange] public function getArrayCopy(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'valid_from' => $this->validFrom->format(self::DATE_FORMAT),
            'valid_to' => $this->validTo?->format(self::DATE_FORMAT),
            'weekdays' => $this->weekdays,
            'percentage' => $this->percentage,
            'target' => $this->getTarget(),
            'is_officer' => $this->isOfficer ? 1 : 0,
        ];
    }

    public function __toString()
    {
        $arrayCopy = $this->getArrayCopy();
        $arrayCopy['target'] = $this->getTarget();
        return json_encode($arrayCopy);
    }

    public function getTarget(): int {
        if (!isset($this->config)) {
            $this->config = Factory::fromFile('./../server/config/times.config.php', true);
        }
        $minutesPerWeek = !$this->isOfficer ? $this->config->get('workingMinutesPerWeek')
                : $this->config->get('workingMinutesPerWeekOfficer');
        return floor($minutesPerWeek * 60 * 1000 * $this->percentage / 100 / sizeof($this->weekdays));
    }

    public function isValid(DateTime $date): bool {
        if ($this->validFrom <= $date) {
            if ($this->validTo) {
                if ($date <= $this->validTo) {
                    if (!$this->hasWeekdays) {
                        return true;
                    } else {
                        return in_array($date->format('w'), $this->weekdays);
                    }
                }
            } else {
                if (!$this->hasWeekdays) {
                    return true;
                } else {
                    return in_array($date->format('w'), $this->weekdays);
                }
            }
        }
        return false;
    }


}
