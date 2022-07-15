<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval as CarbonDuration;
use Carbon\CarbonPeriod;

class SLASchedule
{
    public $daily_periods = [
        ['9am', '5pm'],
        ['18:00:00', '11:59:59'],
    ];

    public $days_of_the_week = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
//        'Saturday',
//        'Sunday',
    ];

    public $excluded_dates = [
        ['25 Dec', 'Christmas Day'],
        ['26 Dec', 'Boxing Day'],
    ];

}
