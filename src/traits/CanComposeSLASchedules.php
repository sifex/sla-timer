<?php

namespace Sifex\SlaTimer\Traits;

use Sifex\SlaTimer\Exceptions\SLAException;
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
        // Get the existing one at that index or create a brand-new period
        return array_key_exists($this->currently_attending, $this->agendas)
            ? $this->agendas[$this->currently_attending]
            : $this->agendas[] = new SLAAgenda();
    }

    /**
     * @return SLAAgenda[]
     */
    public function get_normalised_periods(): array
    {
        // Timezone shifts for each time period
        $this->agendas = collect($this->agendas)
            ->each(function (SLAAgenda $agenda) {
                $agenda->time_periods = collect($agenda->time_periods)
                    ->map(fn ($time_period) => $time_period->shiftTimezone($this->timezone))
                    ->toArray();
            })
            ->toArray();
    }

    public function and(): self
    {
        $this->currently_attending++;

        return $this;
    }

    public static function from(string $from): self
    {
        $self = new self();
        $self->everyDay(); // Default to Every Day
        $self->temporary_from_value = $from;

        return $self;
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
