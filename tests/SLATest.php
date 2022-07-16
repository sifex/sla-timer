<?php

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLASchedule;
use function Spatie\PestPluginTestTime\testTime;

//it('tests the SLA across an SLA definition update', function() {
//    $start_time = '09:00:00';
//    $end_time = '17:00:00';
//
//    $start_date = '2022-07-14';
//    $end_date = '2022-07-16';
//
//    $seconds = 0;
//
//    $period = CarbonPeriod::create($start_date, $end_date);
//
//    // Iterate over the period
//
//    $period->forEach(function(Carbon $date) use (&$seconds, $end_time, $start_time) {
//        $seconds += $date->copy()->setTimeFrom($start_time)
//            ->diffInSeconds($date->copy()->setTimeFrom($end_time)
//            );
//    });
//
//    $seconds_2 = 0;
//
//    $period = CarbonPeriod::create($start_date, $end_date)->addFilter(function(Carbon $date) {
//        return $date->isWeekday();
//    })->addFilter(function(Carbon $date) {
////        die($date->dayName);
//        return $date->dayName !== 'Friday';
//    });
//
//    // Iterate over the period
//
//    $period->forEach(function(Carbon $date) use (&$seconds_2, $end_time, $start_time) {
//        $seconds_2 += $date->copy()->setTimeFrom($start_time)
//            ->diffInSeconds($date->copy()->setTimeFrom($end_time)
//            );
//    });
//
//    expect($seconds)->toEqual($seconds_2);
//});

//it('tests the SLA across an SLA definition update2', function() {
//    $sla = new SLA(new SLASchedule()
//        // Describe each period (9am – 5pm, 6pm – 11:59pm)
//        // Describe each day it's on
//        // Describe each day it's been disabled on (holidays)
//        // Describe the effective start date
//    );
//
//    expect($sla->calculate('2022-07-14 23:00:00')->totalSeconds)->toEqual(1);
//});

/**
 * Daily Periods
 */
it('tests the SLA across a short duration', function () {
    $sla = new SLA(
        new SLASchedule([
            ['9am', '5pm'],
            ['18:00:00', '23:59:59'],
        ])
    );

    testTime()->freeze('2022-07-14 09:00:30');
    expect($sla->calculate('2022-07-14 08:59:30')->totalSeconds)->toEqual(30);
});

it('tests the SLA across a shorter duration', function () {
    $sla = new SLA(
        new SLASchedule([
            ['9am', '5pm'],
            ['18:00:00', '23:59:59'],
        ])
    );

    testTime()->freeze('2022-07-14 23:00:11');
    expect($sla->calculate('2022-07-14 23:00:00')->totalSeconds)->toEqual(11);
});

it('tests the SLA across a standard business day', function () {
    $sla = new SLA(
        new SLASchedule([
            ['9am', '5pm'],
        ])
    );

    testTime()->freeze('2022-07-14 18:00:00');
    expect($sla->calculate('2022-07-14 08:00:00')->hours)->toEqual(8);
});

it('tests the SLA across a short duration with custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:30'],
        ])
    );

    testTime()->freeze('2022-07-15 09:00:30'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->totalSeconds)->toEqual(60);
});

it('tests the SLA across a short duration with 2 custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:30'],
            ['09:30:00', '09:30:30'],
        ])
    );

    testTime()->freeze('2022-07-14 09:31:00'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->totalSeconds)->toEqual(60);
});

it('tests the SLA across a short duration with 2 custom periods but they overlap', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
            ['09:00:00', '09:00:01'],
        ])
    );

    testTime()->freeze('2022-07-14 09:00:02'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->totalSeconds)->toEqual(1);
});

it('tests the SLA across a long duration with custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
        ])
    );

    testTime()->freeze('2022-07-31 09:00:02'); // Now
    expect($sla->calculate('2022-07-01 09:00:00')->totalSeconds)->toEqual(31);
});

it('tests the SLA across a medium duration with custom periods', function () {
    $sla_one = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:02'],
        ])
    );

    $sla_two = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
            ['09:00:00', '09:00:02'],
            ['09:00:00', '09:00:02'],
            ['09:00:00', '09:00:02'],
        ])
    );

    testTime()->freeze('2022-07-31 09:00:02'); // Now

    expect($sla_one->calculate('2022-07-01 09:00:00')->totalSeconds)
        ->toEqual($sla_two->calculate('2022-07-01 09:00:00')->totalSeconds);
});

it('tests the SLA across a time zone', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00 AEDT', '09:01:00 AEDT'],
        ])
    );

    testTime()->freeze('2022-07-16 09:02:00')->shiftTimezone('AEDT'); // Now in Australia

    // Expect the SLA to have counted up throughout the Australian Morning
    expect($sla->calculate('2022-07-16 08:59:00 AEDT')->totalSeconds)->toEqual(60)
        // but not across GMT's assets
        ->and($sla->calculate('2022-07-16 08:59:00')->totalSeconds)->toEqual(0);
});

/**
 * Days in effect
 */
it('tests the SLA across the week', function () {
    $sla = new SLA(
        (new SLASchedule([
            ['09:00:00', '09:00:01'],
        ]))->setDaysOfWeek([
            'Monday',
        ])
    );

    testTime()->freeze('2022-07-12 09:30:00'); // Tuesday the 12th of July
    expect($sla->calculate('Monday, 11-July-22 08:59:00')->totalSeconds)->toEqual(1);
});

it('tests the SLA across all weekdays', function () {
    $sla = new SLA(
        (new SLASchedule([
            ['09:00:00', '09:00:01'],
        ]))->onWeekdays()
    );

    testTime()->freeze('2022-07-16 09:30:00'); // Saturday the 16th of July
    expect($sla->calculate('Monday, 11-July-22 08:59:00')->totalSeconds)->toEqual(5)
    ->and(expect($sla->calculate('Saturday, 9-July-22 08:59:00')->totalSeconds)->toEqual(5))
    ->and(expect($sla->calculate('Friday, 8-July-22 08:59:00')->totalSeconds)->toEqual(6));
});

it('tests the SLA across all weekdays with multiple overlapping SLAs', function () {
    $sla = new SLA(
        (new SLASchedule([
            ['09:00:00', '09:00:01'], // +1 second
            ['09:00:00', '09:00:02'], // +1 second
            ['09:00:30', '09:00:31'], // +1 second
        ]))->onWeekdays()             // =3 seconds
    );

    testTime()->freeze('2022-07-16 09:30:00'); // Saturday the 16th of July
    expect($sla->calculate('Monday, 11-July-22 08:59:00')->totalSeconds)->toEqual(3 /* seconds */ * 5 /* days */)
    ->and(expect($sla->calculate('Saturday, 9-July-22 08:59:00')->totalSeconds)->toEqual(3 /* seconds */ * 5 /* days */))
    ->and(expect($sla->calculate('Friday, 8-July-22 08:59:00')->totalSeconds)->toEqual(3 /* seconds */ * 6 /* days */));
});

it('tests a slightly longer daily schedule\'ed SLA across all weekdays with multiple overlapping SLAs', function () {
    $sla = new SLA(
        (new SLASchedule([
            ['09:00:00', '09:00:01'], // +1 second
            ['09:00:00', '09:00:02'], // +1 second
            ['09:30:30', '09:30:45'], // +15 seconds
        ]))->onWeekdays()             // =17 seconds
    );

    testTime()->freeze('2022-07-16 09:30:00'); // Saturday the 16th of July
    expect($sla->calculate('Monday, 11-July-22 08:59:00')->totalSeconds)->toEqual(17 /* seconds */ * 5 /* days */)
    ->and(expect($sla->calculate('Saturday, 9-July-22 08:59:00')->totalSeconds)->toEqual(17 /* seconds */ * 5 /* days */))
    ->and(expect($sla->calculate('Friday, 8-July-22 08:59:00')->totalSeconds)->toEqual(17 /* seconds */ * 6 /* days */));
});


it('tests an even longer daily schedule\'ed SLA across all weekdays with multiple overlapping SLAs', function () {
    $sla = new SLA(
        (new SLASchedule([
            ['09:00:00', '17:30:00'], // +27k seconds
            ['09:00:00', '09:30:00'], // +0 second (overlap)
            ['17:20:00', '17:30:00'], // +0 second (overlap)
        ]))->onWeekdays()             // =27k seconds
    );

    testTime()->freeze('2022-07-16 09:30:00'); // Saturday the 16th of July
    expect($sla->calculate('Monday, 11-July-22 08:59:00')->totalSeconds)->toEqual(30600 /* seconds */ * 5 /* days */);
});
