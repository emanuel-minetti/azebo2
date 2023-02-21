<?php

namespace AzeboLib;

use DateInterval;
use DatePeriod;
use DateTime;

class DaysOfMonth {
    public static function get(DateTime $month): DatePeriod {
        $firstOfMonth = new DateTime();
        $firstOfNextMonth = new DateTime();
        $firstOfMonth->setDate($month->format('Y'), $month->format('n'), 1);
        $firstOfNextMonth->setDate($month->format('Y'), $month->format('n'), 1);
        $firstOfNextMonth->add(new DateInterval('P1M'));
        $oneDay = new DateInterval('P1D');
        return new DatePeriod($firstOfMonth, $oneDay, $firstOfNextMonth);
    }

}