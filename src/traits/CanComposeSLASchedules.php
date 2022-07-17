<?php

namespace Sifex\SlaTimer\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sifex\SlaTimer\Exceptions\SLAException;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLAAgenda;
use Sifex\SlaTimer\SLASchedule;

trait CanComposeSLASchedules
{
    private string $temporary_from_value = '';

    /** @var SLAAgenda[] */
    public array $agendas = [

    ];

    private int $currently_attending = 0;

    private function get_current_period(): SLAAgenda
    {
        // Get the existing one at that index or create a brand new period
        return array_key_exists($this->currently_attending, $this->agendas)
            ? $this->agendas[$this->currently_attending]
            : $this->agendas[] = new SLAAgenda();
    }

    /**
     * @return SLAAgenda[]
     */
    private function get_normalised_periods(): array
    {
        // Timezone shifts for each time period
        $this->agendas = array_map(function ($agenda) {
            array_map(function($time_period) {
                return $time_period->shiftTimezone($this->timezone); // TODO Investigate whether it's shift or set
            }, $agenda->time_periods);
        }, $this->agendas);


        // Removing any overlap in periods
        $new_agendas = array_map(
            function($day_name) {
                return (new SLAAgenda())->setDays([$day_name]);
            },
            array_unique(
                array_map(
                    function($day_name) { return $day_name; },
                    $this->agendas
                )
            )
        );

        $this->agendas = array_map(function($new_agenda) {
            foreach ($this->agendas as $existing_agenda) {
                if(in_array($new_agenda->days[0], $existing_agenda->days)) {
                    foreach ($existing_agenda->time_periods as $time_period) {

                        foreach ($existing_agenda->time_periods as $existing_time_period) {
                            if ($time_period->overlaps($existing_time_period)) {
                                $time_period = SLA::get_combined_area(
                                    $existing_time_period, $time_period
                                );
                                foreach (array_keys($carry, $existing_time_period, true) as $key) {
                                    unset($carry[$key]);
                                }
                            }
                        }

                        $new_agenda->addTimePeriod(
                            $time_period->start->toString(),
                            $time_period->end->toString(),
                        );
                    }
                }
            }
        }, $new_agendas);

        // The hard part
        // 1. 9-5pm Mon - Fri
        // 2. 4-7pm Thurs - Fri
        // into
        // 9-5pm Mon
        // 9-5pm Tue
        // 9-5pm Wed
        // 9-7pm Thu
        // 9-7pm Fri
        return array_map(function ($carry, SLAAgenda $agenda) {
            $new_time_periods = array_map(function($day_name) {

            }, $agenda->days);

            foreach ($carry as $existing_period) {

            }

            $carry[] = $period;

            return $carry;
        }, $this->agendas);
    }

    public function and(): self
    {
        $this->currently_attending++;

        return $this;
    }

    public function from(string $from): self
    {
        $this->everyDay(); // Default to Every Day
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
