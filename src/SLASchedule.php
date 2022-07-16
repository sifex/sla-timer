<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;

class SLASchedule
{
    public function __construct($daily_periods = [])
    {
        if (! empty($daily_periods)) {
            $this->daily_periods = $daily_periods;
        }
    }

    /**
     * This term outset when the schedule should come into effect
     *
     * @var string
     */
    public $valid_from = '1970-01-01 00:00:01';

    public $daily_periods = [
        ['9am', '5pm'],
        ['18:00:00', '23:59:59'],
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

    protected function get_normalised_daily_periods()
    {
        return $this->daily_periods
    }
}
