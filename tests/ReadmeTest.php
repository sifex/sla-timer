<?php

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLABreach;
use Sifex\SlaTimer\SLASchedule;
use function Spatie\PestPluginTestTime\testTime;

/**
 * Daily Periods
 */
it('expects the scheduler to default to every day', function () {
    $sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:30:00')->everyDay()
    );

    $sla->addBreaches([
        new SLABreach('first_response', '45m'),
        new SLABreach('resolution', '24h'),
    ]);

    testTime()->freeze('2022-07-21 09:46:00');
    $status = $sla->status('2022-07-10 09:00:00'); // SLAStatus
    expect($status->breaches)->toHaveCount(2); // SLABreach[]

    $duration = $sla->duration('2022-07-21 08:59:00'); // CarbonInterval
    $duration->forHumans();

});