<?php

use Sifex\SlaTimer\SLAAgenda;

/**
 * Daily Periods
 */
it('tests an agenda', function () {
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

    var_dump(collect($slaAgenda->toPeriods())->map(fn($p) => $p->toString()));

    expect($slaAgenda->toPeriods())->toEqual([]);
});
