<?php

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Sifex\SlaTimer\SLAAgenda;

beforeEach(function() {
    CarbonPeriod::mixin(EnhancedPeriod::class);
});

/**
 * Daily Periods
 */
it('tests an agenda\'s creation of periods', function () {
    $slaAgenda = new SLAAgenda();
    $slaAgenda
        ->addTimePeriod('09:00:00', '17:30:00')
        ->addTimePeriod('09:00:00', '17:30:00')
        ->setDays([
            'monday',
            'TuEsDaY',
            'Wednesday',
            'Thursday',
            'Fri',
            'Saturday',
            'Sunday',
        ]);

    /**
     * Here we're not going to combine these because we'll just rely on Spatie's overlapsAny
     */
    expect($slaAgenda->toPeriods())->toHaveCount(14);
});
