<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use ReflectionException;

class SLA
{
    /**
     * A schedule is a Day and Time combination
     *
     * @var SLASchedule[]
     */
    public array $schedules = [];

    /**
     * Breaches are a simple duration after which the SLA is breached
     *
     * @var SLABreach[]
     */
    private array $breach_definitions = [];

    /**
     * @param  SLASchedule  $schedule
     *
     * @throws ReflectionException
     */
    public function __construct(SLASchedule $schedule)
    {
        CarbonPeriod::mixin(EnhancedPeriod::class);

        $this->addSchedule($schedule);
    }

    public function addBreach(SLABreach $breach)
    {
        $this->breach_definitions[] = $breach;

        return $this;
    }

    public function addBreaches(...$breaches)
    {
        collect($breaches)->each(fn ($b) => $this->addBreach($b));

        return $this;
    }

    public function addSchedule(SLASchedule $definition)
    {
        $this->schedules[] = $definition;
    }

    /**
     * @throws ReflectionException
     */
    public static function fromSchedule(SLASchedule $definition): SLA
    {
        return new self($definition);
    }

    public function startedAt($subject_start_time): SLAStatus
    {
        $main_target_period = $this->get_current_duration(
            // When the SLA started
            Carbon::parse($subject_start_time),

            // From where we want to stop counting the SLA up to
            // TODO Customise the stop time
            Carbon::now()
        );

        // Iterate over the period
        $main_target_period->forEach(function (Carbon $date) {
            var_dump($date->toString());
        });

        $interval = collect($main_target_period)->map(function (Carbon $daily_subject_period) use ($main_target_period) {
            $start_of_day = max($main_target_period->start, $daily_subject_period);
            $end_of_day = min($main_target_period->end, $daily_subject_period->clone()->addHours(24)->subSecond());

            /**
             * Create a 24 period
             */
            $daily_period = CarbonPeriod::create(
                $start_of_day, $end_of_day
            )->setDateInterval(CarbonInterval::seconds());

            /**
             * Grab the enabled schedule, compare this every day to see if we now have a schedule that would
             * supersede it.
             */
            $enabled_schedule = $this->get_enabled_schedule_for_day();

            /**
             * Grab all the overlapping periods from our daily agenda
             */
            $valid_periods_of_sla = $daily_period->overlapAny(
                collect($enabled_schedule->agendas)
                    ->flatMap(fn ($a) => $a->toPeriods($main_target_period))
                    ->toArray()
            );

            /** @var CarbonInterval[] $intervals */
            $intervals = collect($valid_periods_of_sla)
                ->map(fn (CarbonPeriod $carbonPeriod): CarbonInterval => self::calculate_interval($carbonPeriod))
                ->toArray();

            return self::combine_intervals($intervals);
        })->reduce(fn ($carry, $i) => self::combine_intervals([$carry, $i]), CarbonInterval::seconds(0));

        return new SLAStatus(
            collect($this->breach_definitions)->each->check($interval)->toArray(),
            $interval
        );
    }

    private function get_current_duration($subject_start_time, $end_date_time): CarbonPeriod
    {
        return CarbonPeriod::create($subject_start_time, $end_date_time)
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(fn (Carbon $date) => $this->filter_out_excluded_dates($date));
    }

    private function get_enabled_schedule_for_day(): SLASchedule
    {
        return collect($this->schedules)
            ->filter(function ($schedule) {
                return true; // TODO Filter out based on `effective_date`
            })
            ->last();
    }

    private static function calculate_interval($period): CarbonInterval
    {
        return CarbonInterval::seconds($period->end->unix() - $period->start->unix());
    }

    private static function combine_intervals(array $intervals): CarbonInterval
    {
        return collect($intervals)
            ->reduce(function (CarbonInterval $i, CarbonInterval $overlapping_period) {
                return $i->add($overlapping_period->cascade())->cascade();
            }, CarbonInterval::seconds(0))
            ->cascade();
    }

    /**
     * Filter only the days of the week in the schedule
     *
     * @param  Carbon  $date
     * @return bool
     */
    private function filter_in_days_of_week_in_schedule(Carbon $date): bool
    {
        foreach ($this->schedules as $schedule) {

            // TODO add a start validity here

            foreach ($schedule->agendas as $agenda) {
                return (bool) count($agenda->getPeriodsForDay($date->dayName));
            }
        }

        return false;
    }

    /**
     * Filter out any excluded dates
     *
     * @param  Carbon  $date
     * @return bool
     */
    private function filter_out_excluded_dates(Carbon $date): bool
    {
        return true;
    }
}
