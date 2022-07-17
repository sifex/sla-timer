<?php

namespace Sifex\SlaTimer\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Exceptions\SLAException;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLAProgramme;
use Sifex\SlaTimer\SLASchedule;

trait CanComposeSLASchedules
{
    private string $temporary_from_value = '';

    /** @var SLAProgramme[] */
    public array $periods = [

    ];

    private int $currently_attending = 0;

    private function get_current_period(): SLAProgramme
    {
        // Get the existing one at that index or create a brand new period
        return array_key_exists($this->currently_attending, $this->periods)
            ? $this->periods[$this->currently_attending]
            : $this->periods[] = new SLAProgramme();
    }

    /**
     * @return SLAProgramme[]
     */
    private function get_normalised_periods(): array
    {
        /** @var CarbonPeriod[] $periods */
        $periods = array_map(function ($string_period) {

//            return CarbonPeriod::create(
//                Carbon::now()->setTimezone($schedule->timezone)->setTimeFrom($string_period[0]),
//                Carbon::now()->setTimezone($schedule->timezone)->setTimeFrom($string_period[1])
//            );
        }, $this->periods);

        return array_reduce($periods, function ($carry, CarbonPeriod $period) {
            foreach ($carry as $existing_period) {
                if ($period->overlaps($existing_period)) {
                    $period = SLA::get_combined_area(
                        $existing_period, $period
                    );
                    foreach (array_keys($carry, $existing_period, true) as $key) {
                        unset($carry[$key]);
                    }
                }
            }

            $carry[] = $period;

            return $carry;
        }, []);
    }

    public function and(): self
    {
        $this->currently_attending++;

        return $this;
    }

    public function from(string $from): self
    {
        $this->everyDay();
        $this->temporary_from_value = $from;

        return $this;
    }

    public function andFrom(string $from): self
    {
        $this->currently_attending++;

        return $this->from($from);
    }

    public function to(string $to): self
    {
        if (! $this->temporary_from_value) {
//            throw new SLAException('You haven\'t set a from value');
        }

        $this->get_current_period()->addTimePeriod(
            $this->temporary_from_value, $to
        );

        return $this;
    }

    /**
     * @param  string|array  $days
     * @return SLASchedule|CanComposeSLASchedules
     *
     * @throws SLAException
     */
    public function on($days): self
    {
        if (gettype($days) === 'string') {
            $days = [$days];
        }

        $this->get_current_period()->setDays($days);

        return $this;
    }

    /**
     * @throws SLAException
     */
    public function onWeekdays(): self
    {
        return $this->on([
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
        ]);
    }

    /**
     * @throws SLAException
     */
    public function everyDay(): self
    {
        return $this->on([
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ]);
    }

    /**
     * @throws SLAException
     */
    public function onWeekends(): self
    {
        return $this->on([
            'Saturday',
            'Sunday',
        ]);
    }
}
