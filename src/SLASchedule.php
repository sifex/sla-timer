<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

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

    public function get_normalised_daily_periods()
    {
        /** @var CarbonPeriod[] $periods */
        $periods = array_map(function ($string_period) {
            return CarbonPeriod::create(
                Carbon::now()->setTimeFrom($string_period[0]),
                Carbon::now()->setTimeFrom($string_period[1])
            );
        }, $this->daily_periods);

        return array_reduce($periods, function ($carry, CarbonPeriod $period) {
            foreach ($carry as $existing_period) {
                if ($period->overlaps($existing_period)) {
                    $period = SLA::get_combined_area(
                        $existing_period, $period
                    );
                    foreach (array_keys($carry, $existing_period, true) as $key) {
                        unset($carry[$key]);
                    }
                }
            }

            $carry[] = $period;

            return $carry;
        }, []);
    }
}
