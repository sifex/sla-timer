<?php

namespace Sifex\SlaTimer\Traits;

use Sifex\SlaTimer\Exceptions\SLAException;
use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLAProgramme;
use Sifex\SlaTimer\SLASchedule;

trait CanComposeSLASchedules
{
    private array $schedule_periods = [];
    private int $index_of_currently_composed_period = 0;

    private string $temporary_from_value = '';

    public function __construct(array $schedule_periods = [])
    {
        $this->schedule_periods = $schedule_periods;
    }

    /** @var SLAProgramme[] */
    private array $periods = [

    ];

    private int $currently_attending = 0;

    private function get_current_period(): SLAProgramme
    {
        // Get the existing one at that index or create a brand new period
        return array_key_exists($this->currently_attending, $this->periods)
            ? $this->periods[$this->currently_attending]
            : $this->periods[] = new SLAProgramme();
    }

    public function and(string $from): self
    {
        $this->currently_attending++;

        return $this;
    }

    public function from(string $from): self
    {
        $this->temporary_from_value = $from;

        return $this;
    }

    public function andFrom(string $from): self
    {
        $this->currently_attending++;

        return $this->from($from);
    }

    /**
     * @throws SLAException
     */
    public function to(string $to): self
    {
        if(!$this->temporary_from_value) {
            throw new SLAException('You haven\'t set a from value');
        }

        $this->get_current_period()->addTimePeriod(
            $this->temporary_from_value, $to
        );

        return $this;
    }

    /**
     * @param string|array $days
     * @return SLASchedule|CanComposeSLASchedules
     * @throws SLAException
     */
    public function on($days): self
    {
        if(gettype($days) === 'string') {
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
    public function onWeekends(): self
    {
        return $this->on([
            'Saturday',
            'Sunday',
        ]);
    }
}
