<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Sifex\SlaTimer\SLAAgenda;
use function Spatie\PestPluginTestTime\testTime;


beforeEach(function() {
    CarbonPeriod::mixin(EnhancedPeriod::class);
});

/**
 * Daily Periods
 */
it('tests carbon', function () {
    testTime()->freeze('2022-07-20 10:30:00');

    $period = CarbonPeriod::create(
        '2022-07-17 09:00:00',
        Carbon::now()
    )->setDateInterval(CarbonInterval::seconds());

    $sla = collect(['Wednesday'])
        ->map(function ($d): CarbonPeriod {
            return CarbonPeriod::create(
                Carbon::parse($d)->setTimeFromTimeString('09:00:00'),
                Carbon::parse($d)->setTimeFromTimeString('17:30:00'),
            )->setDateInterval(CarbonInterval::seconds());
        })
        ->toArray();

    expect(
        collect($period->overlapAny($sla))
            ->reduce(function (CarbonInterval $i, CarbonPeriod $overlapping_period) {
                return $i->add(
                    CarbonInterval::seconds(
                        $overlapping_period->start->diffInSeconds($overlapping_period->end)
                    )
                );
            }, CarbonInterval::seconds(0))
            ->cascade()
            ->totalSeconds
    )->toEqual([]);
});
