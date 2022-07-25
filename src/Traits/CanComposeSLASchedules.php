<?php

namespace Sifex\SlaTimer\Traits;

use Sifex\SlaTimer\Agenda\Weekly;
use Sifex\SlaTimer\Interfaces\AgendaInterface;
use Sifex\SlaTimer\SLASchedule;

trait CanComposeSLASchedules
{
    private int $agenda_index = 0;

    /** @var AgendaInterface[] */
    public array $agendas = [

    ];

    private string $temporary_from_value;

    private function get_current_agenda(): AgendaInterface
    {
        // Get the existing one at that index or create a brand-new period
        return array_key_exists($this->agenda_index, $this->agendas)
            ? $this->agendas[$this->agenda_index]
            : $this->agendas[] = new Weekly();
    }

    public static function create(): self
    {
        return new self();
    }

    public function and(): self
    {
        $this->agenda_index = $this->agenda_index + 1;

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
        $this->agenda_index = $this->agenda_index + 1;

        return $this->from($from);
    }

    public function to(string $to): self
    {
        if (! $this->temporary_from_value) {
//            throw new SLAException('You haven\'t set a from value');
        }

        $this->get_current_agenda()->addTimePeriod(
            $this->temporary_from_value, $to
        );

        return $this;
    }

    /**
     * @param  string|array  $days
     * @return SLASchedule|CanComposeSLASchedules
     */
    public function on($days): self
    {
        if (gettype($days) === 'string') {
            $days = [$days];
        }

        $this->get_current_agenda()->setDays($days);

        return $this;
    }

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

    public function onWeekends(): self
    {
        return $this->on([
            'Saturday',
            'Sunday',
        ]);
    }
}
