<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

class SLA
{
    /**
     * @var SLASchedule[]
     */
    public array $schedules = [];

    /**
     * @var SLABreach[]
     */
    private array $breach_definitions = [];

    /**
     * @param  SLASchedule  $schedule
     */
    public function __construct(SLASchedule $schedule)
    {
        $this->define_schedule($schedule);
    }

    public function define_schedule(SLASchedule $definition)
    {
        // TODO Normalise SLAs so that no two periods overlap
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
        $current_full_duration->forEach(function (Carbon $day) use ($end_date_time, $subject_start_time, &$seconds) {
            foreach ($this->schedules as $schedule) {

                // TODO add a start validity here

                // TODO get overlapping with the period

                /** @var CarbonPeriod $period */
                foreach (self::get_normalised_daily_periods($schedule) as $period) {
                    $start_of_period = $day->copy()->setTimeFrom($period->start);
                    $end_of_period = $day->copy()->setTimeFrom($period->end);

                    $seconds += self::secondsOfOverlap(
                        CarbonPeriod::create(
                            $start_of_period,
                            $end_of_period
                        ), CarbonPeriod::create(
                            $subject_start_time,
                            $end_date_time
                        )
                    );
                }
            }
        });

        return new SLAStatus([], CarbonInterval::seconds($seconds)->cascade());
    }

    private static function secondsOfOverlap(CarbonPeriod $first_period, CarbonPeriod $second_period): int
    {
        return $first_period->overlaps($second_period)
            ? self::get_overlapping_area($first_period, $second_period)
                ->start->diffInSeconds(
                    self::get_overlapping_area($first_period, $second_period)->end
                )
            : 0;
    }

    public static function get_overlapping_area(CarbonPeriod $first, CarbonPeriod $second): CarbonPeriod
    {
        return new CarbonPeriod(
            max($first->start, $second->start),
            min($first->end, $second->end)
        );
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

    private static function get_normalised_daily_periods(SLASchedule $schedule)
    {

    }
}
