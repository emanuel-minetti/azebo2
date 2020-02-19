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

class Saldo
{
    private $hours;
    private $minutes;
    private $positive;

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

    private function fix() {
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
    public static function createFromHoursAndMinutes($hours = 0, $minutes = 0, $positive = true) {
        $seconds = $hours * 3600 + $minutes * 60;
        return new Saldo($seconds, $positive);
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
    public static function getSum(Saldo $first, Saldo $second)
    {
        $result = clone $first;
        return $result->add($second);
    }

}
