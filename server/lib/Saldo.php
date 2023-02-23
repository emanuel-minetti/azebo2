<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */


namespace AzeboLib;

use DateTime;
use Stringable;

class Saldo implements Stringable
{
    private int $hours;
    private int $minutes;
    private bool $positive;

    private function __construct($seconds = 0, $positive = true)
    {
        $totalMinutes = (int) floor($seconds / 60);
        $this->hours = (int) floor($totalMinutes / 60);
        $this->minutes = (int) ($totalMinutes - $this->hours * 60);
        $this->positive = $positive;
    }

    private function add(Saldo $other): Saldo
    {
        if($this->positive === $other->positive) {
            // both summands have the same sign
            $this->hours += $other->hours;
            $this->minutes += $other->minutes;
            $this->fix();
        } else {
            // summands have different sign
            if ($this->hours > $other->hours ||
                ($this->hours === $other->hours && $this->minutes >= $other->minutes)) {
                // this is absolute bigger or equal other
                $this->hours -= $other->hours;
                $this->minutes -= $other->minutes;
                $this->fix();
            } else {
                // other is absolute bigger
                $this->hours = $other->hours - $this->hours;
                $this->minutes = $other->minutes - $this->minutes;
                $this->fix();
                $this->positive = !$this->positive;
            }
        }
        return $this;
    }

    private function fix(): void {
        if ($this->minutes < 0) {
            $this->minutes += 60;
            $this->hours--;
        } elseif ($this->minutes >= 60) {
            $this->minutes -= 60;
            $this->hours++;
        }
    }

//    public static function createFromSeconds($seconds, $positive = true) {
//        return new Saldo($seconds, $positive);
//    }

    /**
     * @param int $hours
     * @param int $minutes
     * @param bool $positive
     * @return Saldo
     */
    public static function createFromHoursAndMinutes(int $hours = 0, int $minutes = 0, bool $positive = true): Saldo {
        $seconds = $hours * 3600 + $minutes * 60;
        return new Saldo($seconds, $positive);
    }

    public static function createFromBeginAndEnd(DateTime $begin, DateTime $end): Saldo {
        $hours = $end->format('G') - $begin->format('G');
        if ($end->format('i') - $begin->format('i') >= 0) {
            $minutes = $end->format('i') - $begin->format('i');
        } else {
            $minutes = 60 - ($begin->format('i') - $end->format('i'));
            $hours--;
        }
        return self::createFromHoursAndMinutes($hours, $minutes);
    }

    /**
     * @return int
     */
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * @return int
     */
    public function getMinutes(): int
    {
        return $this->minutes;
    }

    /**
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->positive;
    }

    /**
     * @param Saldo $first
     * @param Saldo $second
     * @return Saldo
     */
    public static function getSum(Saldo $first, Saldo $second): Saldo {
        $result = clone $first;
        return $result->add($second);
    }

    public function getAbsoluteMinuteString(): string {
        return $this->minutes;
    }

    public function __toString(): string {
        $minutes = ($this->minutes < 10) ? '0' . $this->minutes : $this->minutes;
        return ($this->positive ? '' : '-') . $this->hours . ':' . $minutes;
    }

}
