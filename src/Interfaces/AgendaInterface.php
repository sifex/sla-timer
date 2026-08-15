<?php

namespace Sifex\SlaTimer\Interfaces;

use Carbon\CarbonPeriod;

interface AgendaInterface
{
    /**
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array;

    public function addTimePeriod(string $start_time, string $end_time): self;

    /**
     * @param  array<int, string>  $days
     */
    public function setDays(array $days): self;
}
