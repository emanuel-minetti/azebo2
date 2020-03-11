<?php
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link     https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Holiday\Controller;

use DateInterval;
use DateTime;
use Exception;

use Laminas\Config\Factory;
use Laminas\Http\Request;
use Laminas\Http\Response;

use AzeboLib\ApiController;
use WorkingTime\Model\WorkingDay;
use Service\AuthorizationService;

class HolidayController extends ApiController
{
    /** @noinspection PhpUnused */
    public function getAction()
    {
        $year = $this->params('year');
        $request = Request::fromString($this->request);
        $response = Response::fromString($this->response);
        if (AuthorizationService::authorize($request, $response, ['GET',])) {
            try {
                $holidays = $this->getHolidays($year);
            } catch (Exception $e) {
                $response->setStatusCode(500);
                $response->setContent($e->getMessage());
                return $response;
            }
            $userId = $request->getQuery()->user_id;
            return $this->processResult($holidays, $userId);
        } else {
            // `response` was set in the call to `AuthorizationService::authorize`
            return $response;
        }
    }

    /**
     * Returns an array containing all legal holidays in Berlin/Germany.
     * Each holiday is an associative array with keys 'date' which is a
     * `DateTime` and 'name' which is a string
     *
     * @param $year number the year to get the legal holidays for
     * @return array an array of legal holidays
     * @throws Exception if a `DateTime` can't be created
     */
    private function getHolidays($year)
    {
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
                    'date' => $this->formatDate( new DateTime("$year/1/1")),
                    'name' => "Neujahr",
                ],
                [
                    'date' => $this->formatDate(new DateTime("$year/3/8")),
                    'name' => "Internationaler Frauentag",
                ],
                [
                    'date' => $this->formatDate(new DateTime("$year/10/3")),
                    'name' => "Tag der deutschen Einheit"
                ],
                [
                    'date' => $this->formatDate(new DateTime("$year/12/25")),
                    'name' => "1. Weihnachtsfeiertag"
                ],
                [
                    'date' => $this->formatDate(new DateTime("$year/12/26")),
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
                    'date' => $this->formatDate($goodFriday),
                    'name' => "Karfreitag"
                ],
                [
                    'date' => $this->formatDate($easterMonday),
                    'name' => "Ostermontag"
                ],
                [
                    'date' => $this->formatDate($ascension),
                    'name' => "Christi Himmelfahrt"
                ],
                [
                    'date' => $this->formatDate($whitMonday),
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
                        'date' => $this->formatDate($holidayDate),
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

    private function formatDate(DateTime $date) {
        return $date->format(WorkingDay::DATE_FORMAT);
    }
}
