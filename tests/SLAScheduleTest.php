<?php

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Sifex\SlaTimer\SLASchedule;

beforeEach(function () {
    CarbonPeriod::mixin(EnhancedPeriod::class);
});

/**
 * Daily Periods
 */
it('expects the scheduler to default to every day', function () {
    $schedule = (new SLASchedule)->from('09:00')->to('17:30');

    expect(invade($schedule)->agendas)->toHaveCount(1)
        ->and(invade(invade($schedule)->agendas[0])->days)->toHaveCount(7);
});

it('tests the scheduler basic function', function () {
    $schedule = (new SLASchedule)->from('09:00')->to('17:30')->everyDay()
        ->andFrom('10:00')->to('16:30')->onWeekdays();

    expect(invade($schedule)->agendas)->toHaveCount(2);
});
