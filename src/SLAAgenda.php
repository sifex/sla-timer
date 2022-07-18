<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Exceptions\SLAException;

class SLAAgenda
{
    /** @var CarbonPeriod[] */
    public array $time_periods = [];

    /** @var string[] */
    public array $days = [];

    public function addTimePeriod(string $start_time, string $end_time): SLAAgenda
    {
        // TODO Check if any period falls over midnight & throw exception?
        $this->time_periods[] = CarbonPeriod::create(
            Carbon::parse($start_time),
            Carbon::parse($end_time),
        );

        return $this;
    }

    public function addTimePeriods(array $periods): SLAAgenda
    {
        foreach ($periods as $period) {
            $this->addTimePeriod($period[0],$period[1]);
        }

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

    /**
     * @return CarbonPeriod[]
     */
    public function toPeriods(): array
    {
        return collect($this->time_periods)->flatMap(function(CarbonPeriod $time_period) {
            return collect($this->days)->map(function ($day_name) use ($time_period) {
                return CarbonPeriod::create([
                    Carbon::now()->setTimeFrom($time_period->start),
                    Carbon::now()->setTimeFrom($time_period->end),
                ]);
            })->toArray();
        })->toArray();
    }

    /**
     * @param string $day
     * @return CarbonPeriod
     */
    public function getPeriodsForDay(string $day): CarbonPeriod
    {
        return collect($this->toPeriods())->filter(function(CarbonPeriod $period) use ($day) {
            return $period->start->is($day);
        })->first();
    }
}
