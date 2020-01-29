<?php
/**
 * azebo2 is an application to print working time tables
 * Copyright (C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace Holiday\Controller;

use DateInterval;
use DateTime;
use Exception;

use Zend\Config\Factory;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use Service\AuthorizationService;

class HolidayController extends AbstractActionController
{
    /** @noinspection PhpUnused */
    public function getAction()
    {
        $year = $this->params('year');
        $request = Request::fromString($this->request);
        $response = Response::fromString($this->response);
        try {
            $holidays = $this->getHolidays($year);
        } catch (Exception $e) {
            $response->setStatusCode(500);
            $response->setContent($e->getMessage());
            return $response;
        }
        if (AuthorizationService::authorize($request, $response, ['GET',])) {
            // refresh jwt ...
            $userId = $request->getQuery()->user_id;
            $expire = time() + AuthorizationService::EXPIRE_TIME;
            $jwt = AuthorizationService::getJwt($expire, $userId);
            // ... and return response
            return new JsonModel([
                'success' => true,
                'data' => [
                    'jwt' => $jwt,
                    'expire' => $expire,
                    'holidays' => $holidays,
                ],
            ]);
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
        $holidays = [];

        // watch for possible exceptions in `DateTime` constructor
        try {
            array_push($holidays, [
                [
                    'date' => new DateTime("$year/1/1"),
                    'name' => "Neujahr",
                ],
                [
                    'date' => new DateTime("$year/3/8"),
                    'name' => "Internationaler Frauentag",
                ],
                [
                    'date' => new DateTime("$year/10/3"),
                    'name' => "Tag der deutschen Einheit"
                ],
                [
                    'date' => new DateTime("$year/12/25"),
                    'name' => "1. Weihnachtsfeiertag"
                ],
                [
                    'date' => new DateTime("$year/12/26"),
                    'name' => "2. Weihnachtsfeiertag"
                ],
            ]);

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
            array_push($holidays, [
                [
                    'date' => $goodFriday,
                    'name' => "Karfreitag"
                ],
                [
                    'date' => $easterMonday,
                    'name' => "Ostermontag"
                ],
                [
                    'date' => $ascension,
                    'name' => "Christi Himmelfahrt"
                ],
                [
                    'date' => $whitMonday,
                    'name' => "Pfingstmontag"
                ],
            ]);
        } catch (Exception $e) {
            throw new Exception("Could not create `DateTime` object!", 0, $e);
        }

        try {
            $config = Factory::fromFile('./../server/config/holiday.config.php', true);
            foreach ($config as $holiday) {
                if (!isset($holiday->year) || $holiday->year == $year) {
                    $holidayDate = new DateTime("$year/$holiday->month/$holiday->day");
                    $holidays[] = [
                        'date' => $holidayDate,
                        'name' => $holiday->name
                    ];
                }
            }
        } catch (Exception $e) {
            throw new Exception("Holiday configuration could not be read or interpreted", 0, $e);
        }

        return $holidays;
    }
}
