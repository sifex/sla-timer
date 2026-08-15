<?php

namespace Sifex\SlaTimer\Interfaces;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

interface AgendaInterface
{
    /**
     * Returns the agenda periods for the subject period as start/end pairs.
     *
     * @return array<int, array{0: CarbonInterface, 1: CarbonInterface}>
     */
    public function toPeriods(CarbonPeriod $subject_period): array;

    public function addTimePeriod(string $start_time, string $end_time): self;

    /**
     * @param  array<int, string>  $days
     */
    public function setDays(array $days): self;
}
