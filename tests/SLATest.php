<?php

use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
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
    $combined = invade(new SLA((new SLASchedule)->from('')))->combine_intervals([
        $interval_one,
        $interval_two,
        $interval_three,
    ]);

    expect($combined->totalSeconds)->toEqual(30 + (30 * 60) + 180);
});

it('ensures that spatie\'s lib works correctly', function () {
    CarbonPeriod::mixin(EnhancedPeriod::class);

    $period_one = CarbonPeriod::create('Thursday 09:00:00', '1 second', 'Thursday 17:30:00');
    $period_two = CarbonPeriod::create('Friday 09:00:00', '1 second', 'Friday 17:30:00');

    $combined = $period_one->overlapAny($period_two);
    expect($combined)->toHaveCount(0);

    $period_three = CarbonPeriod::create('Thursday 10:30:00', '1 second', 'Thursday 10:45:00');

    $combined = $period_one->overlapAny($period_three);
    expect($combined)->toHaveCount(1);
});

it('pretty much is the entire library in a single test', function () {
    CarbonPeriod::mixin(EnhancedPeriod::class);
    testTime()->freeze('2022-07-21 09:45:00'); // 21st = Thursday

    $example_subject_period = CarbonPeriod::create('2022-07-21 09:00:00', '1 second', '2022-07-21 09:30:00');
    $example_sla_period = CarbonPeriod::create('Thursday 09:00:00', '1 second', 'Thursday 17:30:00');

    $combined = $example_subject_period->overlapAny($example_sla_period);
    $interval = collect($combined)->reduce(
        fn ($carry, $p) => CarbonInterval::seconds($p->end->unix() - $p->start->unix())->add($carry),
        CarbonInterval::seconds(0)
    );

    expect($combined)
        ->and($interval->totalSeconds)->toEqual(1800);
});

it('pretty much is the entire library in a single test over a longer duration ', function () {
    CarbonPeriod::mixin(EnhancedPeriod::class);
    testTime()->freeze('2022-07-21 09:45:00'); // 21st = Thursday

    $example_subject_period = CarbonPeriod::create('2022-07-21 09:00:00', '1 second', '2022-07-21 09:30:00');
    $example_sla_schedule = (new SLASchedule)->from('09:00')->to('17:30')->everyDay()
                                    ->andFrom('09:00')->to('09:30')->everyDay();

    $example_sla_periods = collect(invade($example_sla_schedule)->agendas)->flatMap(function ($a) use ($example_subject_period) {
        return $a->toPeriods($example_subject_period);
    })->toArray();

    $combined = $example_subject_period->overlapAny($example_sla_periods);
    $interval = collect($combined)->reduce(
        fn ($carry, $p) => CarbonInterval::seconds($p->end->unix() - $p->start->unix())->add($carry),
        CarbonInterval::seconds(0)
    );

    expect($combined)
        ->and($interval->totalSeconds)->toEqual(1800);
});

it('tests the SLA across a short duration', function () {
    $sla = new SLA(
        (new SLASchedule)->from('09:00:00')->to('17:00:00')->everyDay()
    );

    testTime()->freeze('2022-07-17 09:00:30');
    expect($sla->startedAt('2022-07-17 08:59:00')->interval->totalSeconds)->toEqual(30);
});

it('tests the SLA with breaches', function () {
    $sla = new SLA(
        (new SLASchedule)->from('09:00:00')->to('17:00:00')->everyDay()
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze('2022-07-17 09:00:30');
    expect($sla->startedAt('2022-07-17 08:59:00')->interval->totalSeconds)->toEqual(30)
        ->and(expect($sla->startedAt('2022-07-17 08:59:00')->breaches[0]->breached)->toEqual(true))
        ->and(expect($sla->startedAt('2022-07-17 08:59:00')->breaches[1]->breached)->toEqual(false));
});

it('tests the SLA over certain days', function () {
    $sla = SLA::fromSchedule(
        (new SLASchedule)->from('09:00:00')->to('17:00:00')->on('Thursdays')
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze('2022-07-21 09:00:30');
    expect($sla->startedAt('2022-07-21 08:59:00')->interval->totalSeconds)->toEqual(30)
        ->and(expect($sla->startedAt('2022-07-21 08:59:00')->breaches[0]->breached)->toEqual(true))
        ->and(expect($sla->startedAt('2022-07-21 08:59:00')->breaches[1]->breached)->toEqual(false));
});

it('tests the SLA over certain other days', function () {
    $sla = SLA::fromSchedule(
        (new SLASchedule)->from('09:00:00')->to('17:00:00')->on('Wednesdays')
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze('2022-07-21 09:00:30');
    expect($sla->startedAt('2022-07-21 08:59:00')->interval->totalSeconds)->toEqual(0)
        ->and(expect($sla->startedAt('2022-07-21 08:59:00')->breaches[0]->breached)->toEqual(false))
        ->and(expect($sla->startedAt('2022-07-21 08:59:00')->breaches[1]->breached)->toEqual(false));
});

it('tests the SLA with double declaration of SLAs', function () {
    $sla = SLA::fromSchedule(
        (new SLASchedule)->from('09:00:00')->to('17:00:00')->on('Thursday')
            ->and()->from('09:00:00')->to('17:00:00')->on('Thursday')
    );

    $sla->addBreaches(
        new SLABreach('Time to First Response', '29s'),
        new SLABreach('Time to Resolution', '31s'),
    );

    testTime()->freeze('2022-07-21 09:00:30');
    expect($sla->startedAt('2022-07-21 08:59:00')->interval->totalSeconds)->toEqual(30);
});
