<?php

namespace Sifex\SlaTimer\Agendas;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Trai\IsAnAgenda;

class Weekly implements IsAnAgenda
{
    /** @var string[] */
    private array $time_periods = [];

    /** @var string[] */
    private array $days = [];

    public function addTimePeriod(string $start_time, string $end_time): Weekly
    {
        $this->time_periods[] = [$start_time, $end_time];

        return $this;
    }

    public function addTimePeriods(...$periods): Weekly
    {
        collect($periods)->flatten()->each(function ($period) {
            $this->addTimePeriod($period[0], $period[1]);
        });

        return $this;
    }

    public function clearTimePeriods(): Weekly
    {
        $this->time_periods = [];

        return $this;
    }

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
     * @param  CarbonPeriod  $subject_period
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array
    {
        $start_date = $subject_period->start->clone();
        $end_date = $subject_period->end->clone();

        $new_period = CarbonPeriod::start($start_date)->end($end_date)->setDateInterval(CarbonInterval::day());

        return collect($new_period)
            ->filter(function (Carbon $day) {
                return collect($this->days)->contains($day->dayName);
            })
            ->flatMap(function (Carbon $day) {
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

    public function getPeriods(CarbonPeriod $subject_period)
    {


    }
}
