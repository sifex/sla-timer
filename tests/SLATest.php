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
//    expect($sla->calculate('2022-07-14 23:00:00')->seconds)->toEqual(1);
//});

it('tests the SLA across a shorter duration', function () {
    $sla = new SLA(
        new SLASchedule()
    );

    testTime()->freeze('2022-07-14 23:00:11');
    expect($sla->calculate('2022-07-14 23:00:00')->seconds)->toEqual(11);
});

it('tests the SLA across a short duration', function () {
    testTime()->freeze('2022-07-14 09:00:30');

    $sla = new SLA(
        new SLASchedule()
    );

    expect($sla->calculate('2022-07-14 08:59:30')->seconds)->toEqual(30);
});

it('tests the SLA across a short duration with custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:30'],
        ])
    );

    testTime()->freeze('2022-07-15 09:00:30'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->seconds)->toEqual(60);
});

it('tests the SLA across a short duration with 2 custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:30'],
            ['09:30:00', '09:30:30'],
        ])
    );

    testTime()->freeze('2022-07-14 09:31:00'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->seconds)->toEqual(60);
});

it('tests the SLA across a short duration with 2 custom periods but they overlap', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
            ['09:00:00', '09:00:01'],
        ])
    );

    testTime()->freeze('2022-07-14 09:00:02'); // Now
    expect($sla->calculate('2022-07-14 09:00:00')->seconds)->toEqual(1);
});

it('tests the SLA across a long duration with custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
        ])
    );

    testTime()->freeze('2022-07-31 09:00:02'); // Now
    expect($sla->calculate('2022-07-01 09:00:00')->seconds)->toEqual(31);
});

it('tests the SLA across a medium duration with custom periods', function () {
    $sla = new SLA(
        new SLASchedule([
            ['09:00:00', '09:00:01'],
            ['09:00:00', '09:00:02'],
        ])
    );

    testTime()->freeze('2022-07-31 09:00:02'); // Now
    expect($sla->calculate('2022-07-01 09:00:00')->seconds)->toEqual(31);
});
