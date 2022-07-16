<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;


class SLASchedule
{
    public $timezone = 'UTC';

    public function __construct($daily_periods = [])
    {
        if (! empty($daily_periods)) {
            $this->daily_periods = $daily_periods;
        }
    }

    public function setTimezone(string $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * This term outset when the schedule should come into effect
     *
     * @var string
     */
    public $valid_from = '1970-01-01 00:00:01';

    public $daily_periods = [

    ];

    /**
     * Comparison using the `Carbon::is` function
     *
     * @var string[]
     */
    public $days_of_the_week = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    /**
     * Comparison using the `Carbon::is` function
     */
    public $excluded_dates = [
        ['25 Dec', 'Christmas Day'],
        ['26 Dec', 'Boxing Day'],
    ];

    /**
     * Comparison using the `Carbon::is` function
     */
    public $excluded_time_periods = [
        ['2022-01-01 00:00:01', '2022-02-01 00:00:01', 'Description'],
    ];


}
