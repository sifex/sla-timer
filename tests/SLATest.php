<?php

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLASchedule;
use function Spatie\PestPluginTestTime\testTime;

/**
 * Daily Periods
 */
it('tests the SLA across a short duration', function () {
    $sla = SLA::fromSchedule(
        (new SLASchedule)->from('09:00:00')->to('09:00:01')
    );

    testTime()->freeze('2022-07-14 09:00:30');
    expect($sla->startedAt('2022-07-14 08:59:30')->interval->totalSeconds)
        ->toEqual(1);
});
//
