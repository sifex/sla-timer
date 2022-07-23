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

    $sla = new SLA(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);
});

it('tests the SLA across a long duration', function () {
    $subject_start_time = '2022-06-17 08:59:00';
    $time_now = '2022-07-17 09:00:30';

    $sla = new SLA(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(864030);
});

it('tests the SLA with breaches', function () {
    $subject_start_time = '2022-07-17 08:59:00';
    $time_now = '2022-07-17 09:00:30';

    $sla = new SLA(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30)
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
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30)
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
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(0)
        ->and(expect($sla->status($subject_start_time)->breaches)->toHaveCount(0));
});

it('tests the SLA with double declaration of SLAs', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->on('Thursday')
            ->and()->from('09:00:00')->to('17:00:00')->on('Thursday')
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);
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
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);

    $sla->addPause('2022-07-21 09:00:00', '2022-07-21 09:00:19');
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(10);

    $sla->clearPausePeriods();
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);
});

it('tests SLA vacations', function () {
    $subject_start_time = '2022-07-21 08:59:00';
    $time_now = '2022-07-21 09:00:30';

    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')
    );

    testTime()->freeze($time_now);
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);

    $sla->addHoliday('2022-07-21');
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(0);

    $sla->clearPausePeriods();
    expect($sla->status($subject_start_time)->interval->totalSeconds)->toEqual(30);
});
