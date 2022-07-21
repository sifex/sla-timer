<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class SLAAgenda
{
    /** @var string[] */
    private array $time_periods = [];

    /** @var string[] */
    private array $days = [];

    public function addTimePeriod(string $start_time, string $end_time): SLAAgenda
    {
        $this->time_periods[] = [$start_time, $end_time];

        return $this;
    }

    public function addTimePeriods(array $periods): SLAAgenda
    {
        collect($periods)->each(function ($period) {
            $this->addTimePeriod($period[0], $period[1]);
        });

        return $this;
    }

    public function clearTimePeriods(): SLAAgenda
    {
        $this->time_periods = [];

        return $this;
    }

    public function setDays(array $days): SLAAgenda
    {
        $this->days = [];

        foreach ($days as $day) {
            $this->days[] = Carbon::parse($day)->dayName;
        }

        return $this;
    }

    /**
     * @return CarbonPeriod[]
     */
    public function toPeriods(): array
    {
        return collect($this->days)
            ->flatMap(function ($day_name) {
                return collect($this->time_periods)
                    ->mapSpread(function ($start_time, $end_time) use ($day_name) {
                        return CarbonPeriod::create(
                            Carbon::parse($day_name)->setTimeFrom($start_time),
                            Carbon::parse($day_name)->setTimeFrom($end_time)
                        )->setDateInterval(CarbonInterval::seconds());
                    });
            })->toArray();
    }

    /**
     * @param  string  $day
     * @return CarbonPeriod[]
     */
    public function getPeriodsForDay(string $day): array
    {
        return collect($this->toPeriods())->filter(function (CarbonPeriod $period) use ($day) {
            return $period->start->is($day);
        })->toArray();
    }
}
