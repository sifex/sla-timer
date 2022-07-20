<?php

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLASchedule;
use function Spatie\PestPluginTestTime\testTime;

/**
 * Daily Periods
 */
//it('tests the SLA across a short duration', function () {
//    $sla = new SLA(
//        SLASchedule::from('09:00:00')->to('17:00:00')->everyDay()
//    );
//
//    testTime()->freeze('2022-07-17 09:00:30');
//    expect($sla->startedAt('2022-07-17 08:59:00')->interval->totalSeconds)->toEqual(30);
//});
