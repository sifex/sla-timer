<?php

namespace Sifex\SlaTimer\Agenda;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Interfaces\AgendaInterface;

class Weekly implements AgendaInterface
{
    /** @var array<int, array{0: string, 1: string}> */
    public array $time_periods = [];

    /** @var string[] */
    public array $days = [];

    public function addTimePeriod(string $start_time, string $end_time): Weekly
    {
        $this->time_periods[] = [$start_time, $end_time];

        return $this;
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $periods
     */
    public function addTimePeriods(...$periods): Weekly
    {
        collect([$periods])->flatten(1)->each(function ($period) {
            $this->addTimePeriod($period[0], $period[1]);
        });

        return $this;
    }

    public function clearTimePeriods(): Weekly
    {
        $this->time_periods = [];

        return $this;
    }

    /**
     * @param  array<int, string>  $days
     */
    public function setDays(array $days): Weekly
    {
        $this->days = [];

        foreach ($days as $day) {
            $this->days[] = Carbon::parse($day)->dayName;
        }

        return $this;
    }

    /**
     * This is a pretty hacky workaround, but in order for our spatie/period package to calculate the correct overlap,
     * we need to generate a full number of periods surrounding/covering our subject period, because Carbon is not
     * capable of generating a full infinite series of 'Fridays 9am to 5pm', so we have to do the heavy lifting for it
     *
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array
    {
        $start_date = $subject_period->start;
        $end_date = $subject_period->end;

        if ($start_date === null || $end_date === null) {
            return [];
        }

        $new_period = CarbonPeriod::create(Carbon::parse($start_date)->startOfDay(), Carbon::parse($end_date)->startOfDay())
            ->setDateInterval(CarbonInterval::day());

        return collect(iterator_to_array($new_period))
            ->filter(function (CarbonInterface $day) {
                return collect($this->days)->contains($day->dayName);
            })
            ->flatMap(function (CarbonInterface $day) {
                return collect($this->time_periods)
                    ->map(function (array $t) use ($day) {
                        return CarbonPeriod::create(
                            $day->clone()->setTimeFromTimeString($t[0]),
                            '1 second',
                            $day->clone()->setTimeFromTimeString($t[1]),
                        );
                    });
            })->toArray();
    }
}
