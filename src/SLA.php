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

    public function latestSchedule(): SLASchedule
    {
        return end($this->schedules);
    }

    public function startedAt($subject_start_time): SLAStatus
    {
        // When the SLA started
        $subject_start_time = Carbon::parse($subject_start_time);

        // From where we want to stop counting the SLA up to
        // TODO Customise the stop time
        $end_date_time = Carbon::now();

        // Grab every day between the two dates
        $current_full_duration = CarbonPeriod::create($subject_start_time, $end_date_time)
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(fn (Carbon $date) => $this->filter_in_days_of_week_in_schedule($date))
            ->addFilter(fn (Carbon $date) => $this->filter_out_excluded_dates($date));

        // Iterate over the period
        $interval = collect($current_full_duration)->map(function (Carbon $daily_subject_period) {
            $daily_period = CarbonPeriod::create(
                $daily_subject_period,
                $daily_subject_period->clone()->addHours(24)->subSeconds(1)
            );

            /**
             * Grab the enabled schedule, compare this every day to see if we now have a superseded schedule
             */
            /** @var SLASchedule $enabled_schedule */
            $enabled_schedule = collect($this->schedules)
                ->filter(function ($schedule) {
                    return true; // TODO Filter out based on `effective_date`
                })
                ->last();

            /**
             * Grab all the overlapping periods from our daily agenda
             */
            /** @var CarbonPeriod[] $valid_periods_of_sla */
            $valid_periods_of_sla = $daily_period->overlapAny(
                collect($enabled_schedule->agendas)->flatMap(function (SLAAgenda $agenda) use ($daily_period) {
                    return $agenda->getPeriodsForDay($daily_period->start->dayName);
                })->whereNotNull()->toArray()
            );

            $intervals = collect($valid_periods_of_sla)->map(function (CarbonPeriod $carbonPeriod) {
                return $carbonPeriod->interval;
            })->toArray();

            return self::collapse_carbon_intervals($intervals);
        })->toArray();

        return new SLAStatus([/* Breaches */], self::collapse_carbon_intervals($interval));
    }

    private static function collapse_carbon_intervals(array $intervals): CarbonInterval
    {
        return collect($intervals)
            ->reduce(function (CarbonInterval $i, CarbonPeriod $overlapping_period) {
                return $i->add(
                    CarbonInterval::seconds(
                        $overlapping_period->start->diffInSeconds($overlapping_period->end)
                    )
                );
            }, CarbonInterval::seconds(0))
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
                foreach ($agenda->days as $day_name) {
                    if ($date->is($day_name)) {
                        return true;
                    }
                }
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
