<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\SLA;


it('tests the SLA across an SLA definition update', function() {
    $start_time = '09:00:00';
    $end_time = '17:00:00';

    $start_date = '2022-07-14';
    $end_date = '2022-07-16';

    $seconds = 0;

    $period = CarbonPeriod::create($start_date, $end_date);

    // Iterate over the period

    $period->forEach(function(Carbon $date) use (&$seconds, $end_time, $start_time) {
        $seconds += $date->copy()->setTimeFrom($start_time)
            ->diffInSeconds($date->copy()->setTimeFrom($end_time)
            );
    });

    $seconds_2 = 0;

    $period = CarbonPeriod::create($start_date, $end_date)->addFilter(function(Carbon $date) {
        return $date->isWeekday();
    })->addFilter(function(Carbon $date) {
//        die($date->dayName);
        return $date->dayName !== 'Friday';
    });

    // Iterate over the period

    $period->forEach(function(Carbon $date) use (&$seconds_2, $end_time, $start_time) {
        $seconds_2 += $date->copy()->setTimeFrom($start_time)
            ->diffInSeconds($date->copy()->setTimeFrom($end_time)
            );
    });

    expect($seconds)->toEqual($seconds_2);
});


it('tests the SLA across an SLA definition update2', function() {
    $sla = new SLA([], ''
        // Describe each period (9am – 5pm, 6pm – 11:59pm)
        // Describe each day it's on
        // Describe each day it's been disabled on (holidays)
        // Describe the effective start date
    );
});
