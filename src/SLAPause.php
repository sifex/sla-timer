<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SLAPause
{
    public string $start_time;

    public string $end_time;

    public function __construct(string $start_time, string $end_time)
    {
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    /**
     * Used more for Pausing an SLA
     *
     * @return \DatePeriod
     */
    public function toPeriod(): \DatePeriod
    {
        return CarbonPeriod::create(
            Carbon::parse($this->start_time),
            Carbon::parse($this->end_time),
        )->toDatePeriod();
    }

    /**
     * Used more for Holidays
     *
     * @return \DatePeriod
     */
    public function toDayPeriod(): \DatePeriod
    {
        return CarbonPeriod::create(
            Carbon::parse($this->start_time)->startOfDay(),
            Carbon::parse($this->end_time)->endOfDay(),
        )->toDatePeriod();
    }
}
