<?php

namespace Service;

use DateInterval;
use DateTime;
use Exception;
use Laminas\Config\Factory;
use WorkingTime\Model\WorkingDay;

class HolidayService {
    /**
     * Returns an array containing all legal holidays in Berlin/Germany.
     * Each holiday is an associative array with keys 'date' which is a
     * `DateTime` and 'name' which is a string
     *
     * @param $year int the year to get the legal holidays for
     * @return array an array of legal holidays
     * @throws Exception if a `DateTime` can't be created
     */
    public static function getHolidays(int $year):array {
        /*
        * Die festen gesetzlichen Feiertage in Berlin sind:
        *
        * -Neujahr (1.1.)
        * -Internationaler Frauentag (8.3. ab dem Jahr 2019)
        * -Tag der Arbeit (1.5.)
        * -Tag der dt. Einheit (3.10.)
        * -1. Weihnachtsfeiertag (25.12.)
        * -2. Weihnachtsfeiertag (26.12.)
        *
        * Die beweglichen gesetzlichen Feiertage in Berlin sind:
        *
        * -Karfreitag (Ostersonntag - 2)
        * -Ostermontag (Ostersonntag + 1)
        * -Christi Himmelfahrt (Ostersonntag + 39)
        * -Pfingstmontag (Ostersonntag + 50)
        */

        // watch for possible exceptions in `DateTime` constructor
        try {
            //add fixed holidays
            $holidays = [
                [
                    'date' => self::formatDate( new DateTime("$year/1/1")),
                    'name' => "Neujahr",
                ],
                [
                    'date' => self::formatDate(new DateTime("$year/3/8")),
                    'name' => "Internationaler Frauentag",
                ],[
                    'date' => self::formatDate(new DateTime("$year/5/1")),
                    'name' => "Tag der Arbeit",
                ],
                [
                    'date' => self::formatDate(new DateTime("$year/10/3")),
                    'name' => "Tag der deutschen Einheit"
                ],
                [
                    'date' => self::formatDate(new DateTime("$year/12/25")),
                    'name' => "1. Weihnachtsfeiertag"
                ],
                [
                    'date' => self::formatDate(new DateTime("$year/12/26")),
                    'name' => "2. Weihnachtsfeiertag"
                ],
            ];

            // compute movable holidays
            $easterDays = easter_days($year);
            $equinox = new DateTime("$year/03/21");
            $easter = clone $equinox;
            $easter->add(new DateInterval("P{$easterDays}D"));
            $goodFriday = clone $easter;
            $goodFriday->sub(new DateInterval("P2D"));
            $easterMonday = clone $easter;
            $easterMonday->add(new DateInterval("P1D"));
            $ascension = clone $easter;
            $ascension->add(new DateInterval("P39D"));
            $whitMonday = clone $easter;
            $whitMonday->add(new DateInterval("P50D"));

            // add movable holidays
            $holidays = array_merge($holidays, [
                [
                    'date' => self::formatDate($goodFriday),
                    'name' => "Karfreitag"
                ],
                [
                    'date' => self::formatDate($easterMonday),
                    'name' => "Ostermontag"
                ],
                [
                    'date' => self::formatDate($ascension),
                    'name' => "Christi Himmelfahrt"
                ],
                [
                    'date' => self::formatDate($whitMonday),
                    'name' => "Pfingstmontag"
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception("Could not create `DateTime` object!", 0, $e);
        }

        try {
            // add configurable holidays
            $config = Factory::fromFile('./../server/config/holiday.config.php', true);
            foreach ($config as $holiday) {
                if (!isset($holiday->year) || $holiday->year == $year) {
                    $holidayDate = new DateTime("$year/$holiday->month/$holiday->day");
                    $holidays[] = [
                        'date' => self::formatDate($holidayDate),
                        'name' => $holiday->name
                    ];
                }
            }
        } catch (Exception $e) {
            throw new Exception("Holiday configuration could not be read or interpreted", 0, $e);
        }
        usort($holidays, function ($a, $b) {
            return $a['date'] > $b['date'] ? 1 : -1;
        });
        return $holidays;
    }

    private static function formatDate(DateTime $date): string {
        return $date->format(WorkingDay::DATE_FORMAT);
    }
}