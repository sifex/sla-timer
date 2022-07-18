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
     * @var SLASchedule[]
     */
    public array $schedules = [];

    /**
     * Breaches are a simple duration after which the SLA is breached
     * @var SLABreach[]
     */
    private array $breach_definitions = [];

    /**
     * @param SLASchedule $schedule
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

    public static function fromSchedule(SLASchedule $definition)
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

        $seconds = 0;

        // Iterate over the period
        $current_full_duration->forEach(function (CarbonPeriod $daily_subject_period) use ($end_date_time, $subject_start_time) {
            /** @var SLASchedule $enabled_schedule */
            $enabled_schedule = collect($this->schedules)
                ->filter(function($schedule) {
                    return true; // TODO Filter out based on `effective_date`
                })
                ->last();

            /** @var CarbonPeriod[] $overlapped_collection */
            $overlapped_collection = $daily_subject_period->overlapAny(
                collect($enabled_schedule->agendas)->flatMap(function(SLAAgenda $agenda) use ($daily_subject_period) {
                    return $agenda->getPeriodsForDay($daily_subject_period->start->dayName);
                })->toArray()
            );

            collect($overlapped_collection)->each(function(CarbonPeriod $overlap) {
                $overlap
            });
        });

        return new SLAStatus([], CarbonInterval::seconds($seconds)->cascade());
    }

    /**
     * @param CarbonPeriod $subject_period
     * @param CarbonPeriod|array ...$sla_periods
     * @return CarbonPeriod[]
     */
    public static function get_sla_coverage(CarbonPeriod $subject_period, ...$sla_periods): array
    {
        /**
         * Here we want to provide back an array of all times the $subject_period has hit across
         * one of the $sla_periods. This is best used per day, but I guess could be used over a longer duration.
         *
         * A          [========]
         * B                      [==]
         * C                           [========]
         * SUBJECT         [==============]
         * OVERLAP         [===]  [==] [==]
         */

        /** @var $sla_periods */
        return collect($sla_periods)
            ->
            ->toArray();
    }

    public static function get_combined_area(CarbonPeriod $first, CarbonPeriod $second): CarbonPeriod
    {
        return new CarbonPeriod(
            min($first->start, $second->start),
            max($first->end, $second->end)
        );
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
//        foreach ($this->schedules as $schedule) {
//
//            // TODO add a start validity here
//
//            foreach ($schedule->days_of_the_week as $day) {
//                if ($date->is($day)) {
//                    return true;
//                }
//            }
//        }
//
//        return false;
    }
}
