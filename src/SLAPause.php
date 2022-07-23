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
     * @return CarbonPeriod
     */
    public function toPeriod(): CarbonPeriod
    {
        return CarbonPeriod::create(
            Carbon::parse($this->start_time),
            Carbon::parse($this->end_time),
        );
    }

    /**
     * Used more for Holidays
     *
     * @return CarbonPeriod
     */
    public function toDayPeriod(): CarbonPeriod
    {
        return CarbonPeriod::create(
            Carbon::parse($this->start_time)->startOfDay(),
            Carbon::parse($this->end_time)->endOfDay(),
        );
    }
}
