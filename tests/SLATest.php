<?php

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLASchedule;

/**
 * Daily Periods
 */
it('tests the SLA across a short duration', function () {
    $sla = new SLA(
        SLASchedule::from('09:00:00')->to('05:00:00')->everyDay()
    );

    var_dump($sla->startedAt('2022-07-17 09:00:00')->interval->totalSeconds);
});
//
