<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Exceptions\SLAException;

class SLAAgenda
{
    /** @var CarbonPeriod[] */
    public array $time_periods = [];

    /** @var CarbonPeriod[] */
    public array $days = [];

    public function addTimePeriod(string $start_time, string $end_time): SLAAgenda
    {
        $this->time_periods[] = CarbonPeriod::create(
            Carbon::parse($start_time),
            Carbon::parse($end_time),
        );

        return $this;
    }

    public function setDays(array $days): SLAAgenda
    {
        $this->days = [];

        try {
            foreach ($days as $day) {
                $this->days[] = Carbon::parse($day)->dayName;
            }
        } catch (\Exception $e) {
            // throw new SLAException('Could not parse day '.$day);
        }

        return $this;
    }
}
