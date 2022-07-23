<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SLAHoliday extends SLAPause
{
    /**
     * Used more for Pausing an SLA
     *
     * @return CarbonPeriod
     */
    public function toPeriod(): CarbonPeriod
    {
        return CarbonPeriod::create(
            Carbon::parse($this->start_time)->startOfDay(),
            Carbon::parse($this->end_time ?? $this->start_time)->endOfDay(),
        );
    }
}
