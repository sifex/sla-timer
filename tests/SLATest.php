<?php

use Carbon\CarbonInterval;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLABreach;
use Sifex\SlaTimer\SLASchedule;
use function Spatie\PestPluginTestTime\testTime;

/**
 * Daily Periods
 */
it('collapses intervals', function () {
    $interval_one = CarbonInterval::seconds(30);
    $interval_two = CarbonInterval::minutes(30);
    $interval_three = CarbonInterval::seconds(180);

    /** @var CarbonInterval $combined */
    $combined = invade(new SLA(SLASchedule::create()->from('')))->combine_intervals([
        $interval_one,
        $interval_two,
        $interval_three,
    ]);

    expect($combined->totalSeconds)->toEqual(30 + (30 * 60) + 180);
});

it('tests the SLA across a short duration', function () {
    $subject_start_time = '2022-07-17 08:59:00';
    $time_now = '2022-07-17 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);
});

it('tests the SLA across a long duration', function () {
    $subject_start_time = '2021-06-17 08:59:00';
    $time_now = '2022-07-17 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(864030);
});

it('tests the SLA with breaches', function () {
    $subject_start_time = '2022-07-17 08:59:00';
    $time_now = '2022-07-17 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30)
        ->and(expect($sla->status($subject_start_time)->breaches)->toHaveCount(1))
        ->and(expect($sla->status($subject_start_time)->breaches[0]->breached)->toEqual(true));
});

it('tests the SLA over certain days', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->on('Thursdays')
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30)
        ->and(expect($sla->status($subject_start_time)->breaches)->toHaveCount(1))
        ->and(expect($sla->status($subject_start_time)->breaches[0]->breached)->toEqual(true));
});

it('tests the SLA over certain other days', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->on('Wednesdays')
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(0)
        ->and(expect($sla->status($subject_start_time)->breaches)->toHaveCount(0));
});

it('tests the SLA with double declaration of SLAs', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()
            ->from('09:00:00')->to('17:00:00')->on('Thursday')->and()
            ->from('09:00:00')->to('17:00:00')->on('Thursday')
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);
});

it('tests SLA stopping', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $subject_stop_time = '2022-07-21 09:00:20';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->on('Thursday')
            ->and()->from('09:00:00')->to('17:00:00')->on('Thursday')
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time, $subject_stop_time)->interval->totalSeconds)->toEqual(20);
});

it('tests SLA pausing', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);

    $sla->addPause('2022-07-21 09:00:00', '2022-07-21 09:00:19');
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(10);

    $sla->clearPausePeriods();
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);
});

it('tests SLA vacations', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-23 10:00:00';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('09:01:00')
    );

    testTime()->freeze($time_now);
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(180);

    $sla->addHoliday('2022-07-22');
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(120);

    $sla->clearPausePeriods();
    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(180);
});

it('tests superseded schedules', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')
    );

    testTime()->freeze($time_now);

    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);

    $sla->addSchedule(
        SLASchedule::create()->effectiveFrom('2022-07-20')->from('09:00:00')->to('09:00:01')->everyDay()
    );

    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(1);
});

it('tests superseded schedules but there was a bug with which day it starts on', function () {
    $time_now = '2022-07-25 10:00:00';

    // Create our initial schedule
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('09:01:00') // 60 seconds
            ->everyDay()
    );

    $sla->addSchedule(
        SLASchedule::create()->effectiveFrom('27-07-2022')
            ->from('09:00:00')->to('09:00:30')->onWeekdays()->and() // 30 seconds
            ->from('09:00:00')->to('09:00:10')->onWeekends() // 10 seconds
    );

    testTime()->freeze('2022-07-24 07:00:00'); // Before Period on the 24th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(0);

    testTime()->freeze('2022-07-24 10:00:00'); // After Period on the 24th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(0);

    testTime()->freeze('2022-07-25 07:00:00'); // Before Period on the 25th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(0);

    testTime()->freeze('2022-07-25 10:00:00'); // After Period on the 25th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(60);

    testTime()->freeze('2022-07-26 07:00:00'); // Before Period on the 26th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(60);

    testTime()->freeze('2022-07-26 10:00:00'); // After Period on the 26th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(120);

    testTime()->freeze('2022-07-27 07:00:00'); // Before Period on the 27th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(120);

    testTime()->freeze('2022-07-27 10:00:00'); // After Period on the 27th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(150);

    testTime()->freeze('2022-07-28 07:00:00'); // Before Period on the 28th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(150);

    testTime()->freeze('2022-07-28 10:00:00'); // After Period on the 28th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(180);

    testTime()->freeze('2022-07-29 07:00:00'); // Before Period on the 29th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(180);

    testTime()->freeze('2022-07-29 10:00:00'); // After Period on the 29th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(210);

    testTime()->freeze('2022-07-30 07:00:00'); // Before Period on the 30th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(210);

    testTime()->freeze('2022-07-30 10:00:00'); // After Period on the 30th
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(220);

    testTime()->freeze('2022-07-31 07:00:00'); // Before Period on the 31st
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(220);

    testTime()->freeze('2022-07-31 10:00:00'); // After Period on the 31st
    expect($sla->duration('2022-07-25 08:59:00')->totalSeconds)->toEqual(230);
});

//it('tests 0 length SLAs', function () {
//    $subject_start_time = '2022-07-21 08:59:00';
//    $time_now = '2022-07-21 09:00:30';
//
//    $sla = SLA::fromSchedule(
//        SLASchedule::create()->from('09:00:00')->to('17:00:00')
//    );
//
//    testTime()->freeze($time_now);
//
//    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(30);
//
//    $sla->addSchedule(
//        SLASchedule::create()->effectiveFrom('2022-07-20')->from('09:00:00')->to('09:00:01')->everyDay()
//    );
//
//    expect($sla->duration($subject_start_time)->totalSeconds)->toEqual(1);
//});
