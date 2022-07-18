<?php

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLASchedule;
use Spatie\Period\Visualizer;
use function Spatie\PestPluginTestTime\testTime;

/**
 * Daily Periods
 */
it('tests the SLA across a short duration', function () {
    $visualizer = new Visualizer(["width" => 100]);



    $visualizer->visualize([
        "A" => Period::make('2021-01-01', '2021-01-31'),
        "B" => Period::make('2021-02-10', '2021-02-20'),
        "C" => Period::make('2021-03-01', '2021-03-31'),
        "D" => Period::make('2021-01-20', '2021-03-10'),
        "OVERLAP" => new PeriodCollection(
            Period::make('2021-01-20', '2021-01-31'),
            Period::make('2021-02-10', '2021-02-20'),
            Period::make('2021-03-01', '2021-03-10')
        ),
    ]);
});
//
