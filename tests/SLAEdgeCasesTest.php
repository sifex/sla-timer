<?php

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLABreach;
use Sifex\SlaTimer\SLAHoliday;
use Sifex\SlaTimer\SLAPause;
use Sifex\SlaTimer\SLASchedule;

use function Spatie\PestPluginTestTime\testTime;

/**
 * Schedule building
 */
it('builds a schedule from a single day name string', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('10:00:00')->on('Saturday')
    );

    $duration = $sla->duration('2023-04-29 00:00:00', '2023-04-29 12:00:00');

    expect($duration->totalSeconds)->toEqual(3600);
});

it('counts SLA time on weekends only', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->onWeekends()
    );

    $duration = $sla->duration('2023-04-27 00:00:00', '2023-05-01 00:00:00');

    expect($duration->totalSeconds)->toEqual(57600);
});

it('supports multiple time periods per day with andFrom', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()
            ->from('09:00:00')->to('12:00:00')
            ->andFrom('13:00:00')->to('17:00:00')
            ->everyDay()
    );

    $duration = $sla->duration('2023-04-27 10:00:00', '2023-04-27 14:00:00');

    expect($duration->totalSeconds)->toEqual(10800);
});

it('adds additional time periods via the weekly agenda', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('10:00:00')->everyDay()
    );

    $sla->schedules[0]->agendas[0]->addTimePeriods(['11:00:00', '12:00:00']);

    $duration = $sla->duration('2023-04-27 08:00:00', '2023-04-27 13:00:00');

    expect($duration->totalSeconds)->toEqual(7200);
});

it('clears time periods via the weekly agenda', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('10:00:00')->everyDay()
    );

    $sla->schedules[0]->agendas[0]->clearTimePeriods();

    $duration = $sla->duration('2023-04-27 08:00:00', '2023-04-27 13:00:00');

    expect($duration->totalSeconds)->toEqual(0);
});

it('stores the schedule timezone', function () {
    $schedule = SLASchedule::create()->from('09:00:00')->to('17:00:00')->setTimezone('Europe/London');

    expect($schedule->timezone)->toEqual('Europe/London');
});

/**
 * Schedule validity
 */
it('returns zero when the schedule is not yet effective', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->effectiveFrom('2099-01-01')->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-27 17:00:00');

    expect($duration->totalSeconds)->toEqual(0);
});

it('switches to the latest effective schedule mid-duration', function () {
    $sla = SLA::fromSchedules([
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay(),
        SLASchedule::create()->effectiveFrom('2023-04-28')->from('09:00:00')->to('10:00:00')->everyDay(),
    ]);

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-29 09:00:00');

    expect($duration->totalSeconds)->toEqual(32400);
});

/**
 * Boundary conditions
 */
it('returns zero when the subject starts after the schedule ends', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 18:00:00', '2023-04-27 19:00:00');

    expect($duration->totalSeconds)->toEqual(0);
});

it('counts a subject starting exactly at the schedule start', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-27 09:00:30');

    expect($duration->totalSeconds)->toEqual(30);
});

it('counts a subject ending exactly at the schedule end', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-27 17:00:00');

    expect($duration->totalSeconds)->toEqual(28800);
});

it('counts SLA time across multiple weeks excluding weekends', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->onWeekdays()
    );

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-05-11 09:00:00');

    expect($duration->totalSeconds)->toEqual(288000);
});

/**
 * Overnight (midnight-crossing) schedules
 */
it('counts an overnight schedule across midnight', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('22:00:00')->to('02:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 21:00:00', '2023-04-28 03:00:00');

    expect($duration->totalSeconds)->toEqual(14400);
});

it('counts an overnight schedule when the subject sits entirely within the night', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('22:00:00')->to('02:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 23:00:00', '2023-04-28 01:00:00');

    expect($duration->totalSeconds)->toEqual(7200);
});

it('counts the full overnight window', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('22:00:00')->to('02:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 22:00:00', '2023-04-28 02:00:00');

    expect($duration->totalSeconds)->toEqual(14400);
});

it('counts an overnight schedule over multiple days', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('22:00:00')->to('02:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 21:00:00', '2023-04-28 23:00:00');

    expect($duration->totalSeconds)->toEqual(18000);
});

it('counts an overnight weekday schedule over multiple days', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('22:00:00')->to('02:00:00')->onWeekdays()
    );

    $duration = $sla->duration('2023-04-27 21:00:00', '2023-04-29 03:00:00');

    expect($duration->totalSeconds)->toEqual(28800);
});

it('treats equal from and to times as a 24 hour schedule', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('09:00:00')->everyDay()
    );

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-28 09:00:00');

    expect($duration->totalSeconds)->toEqual(86400);
});

/**
 * Pauses & holidays
 */
it('builds a pause day period spanning the full day', function () {
    $pause = new SLAPause('2023-04-27 10:00:00', '2023-04-27 14:00:00');

    $period = $pause->toDayPeriod();

    expect($period->start->format('Y-m-d H:i:s'))->toEqual('2023-04-27 00:00:00')
        ->and($period->end->format('Y-m-d H:i:s'))->toEqual('2023-04-27 23:59:59');
});

it('builds a holiday period spanning the full day', function () {
    $holiday = new SLAHoliday('2023-04-27', '2023-04-27');

    $period = $holiday->toPeriod();

    expect($period->start->format('Y-m-d H:i:s'))->toEqual('2023-04-27 00:00:00')
        ->and($period->end->format('Y-m-d H:i:s'))->toEqual('2023-04-27 23:59:59');
});

it('ignores a pause outside of the subject period', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addPause('2023-04-26 00:00:00', '2023-04-26 23:59:59');

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-27 17:00:00');

    expect($duration->totalSeconds)->toEqual(28800);
});

it('returns zero when a pause covers the entire subject period', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addPause('2023-04-27 08:00:00', '2023-04-27 18:00:00');

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-27 17:00:00');

    expect($duration->totalSeconds)->toEqual(0);
});

it('returns zero when a pause spans multiple days', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addPause('2023-04-27 00:00:00', '2023-04-28 23:59:59');

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-29 09:00:00');

    expect($duration->totalSeconds)->toEqual(0);
});

it('accepts multiple holidays as an array', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addHolidays(['2023-04-27', '2023-04-28']);

    $duration = $sla->duration('2023-04-27 09:00:00', '2023-04-29 17:00:00');

    expect($duration->totalSeconds)->toEqual(28800);
});

it('ignores a holiday that falls on a day without SLA coverage', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->onWeekdays()
    );

    $sla->addHoliday('2023-04-29');

    $duration = $sla->duration('2023-04-28 09:00:00', '2023-05-01 17:00:00');

    expect($duration->totalSeconds)->toEqual(57600);
});

/**
 * Breaches
 */
it('returns only breached breaches from the breaches accessor', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addBreaches(
        new SLABreach('fast', '1s'),
        new SLABreach('slow', '1000h'),
    );

    testTime()->freeze('2023-04-27 09:00:05');

    $breaches = $sla->breaches('2023-04-27 09:00:00');

    expect($breaches)->toHaveCount(1)
        ->and($breaches[0]->name)->toEqual('fast');
});

it('does not breach when the interval exactly matches the breach threshold', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('09:00:01')->everyDay()
    );

    $sla->addBreaches(new SLABreach('exact', '1s'));

    testTime()->freeze('2023-04-27 09:00:01');

    $status = $sla->status('2023-04-27 09:00:00');

    expect($status->interval->totalSeconds)->toEqual(1)
        ->and($status->hasABreach())->toBeFalse();
});

it('does not report a breach when no breaches are defined', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze('2023-04-27 09:00:05');

    expect($sla->status('2023-04-27 09:00:00')->hasABreach())->toBeFalse();
});
