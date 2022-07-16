<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use loophp\collection\Collection;

class SLA
{
    /** @var SLASchedule[] */
    private array $schedules = [];

    public function __construct(SLASchedule $schedule)
    {
        $this->defineSLA($schedule);
    }

    public function defineSLA(SLASchedule $definition)
    {
        // TODO Normalise SLAs so that no two periods overlap
        $this->schedules[] = $definition;
    }

    /**
     * ––––– Schedule –––––
     * Start On
     * Stop On
     *
     * Versioning (ie. one SLA before this date, and one after this date)
     * TimeZone
     */
    public function calculate($start_date_time): CarbonInterval
    {
        // When the SLA started
        $start_date_time = Carbon::parse($start_date_time);

        // From where we want to stop counting the SLA up to
        // TODO Customise the stop time
        $end_date_time = Carbon::now();

        // Grab every day between the two dates
        $current_duration = CarbonPeriod::create($start_date_time, $end_date_time)
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(fn(Carbon $date) => $this->filter_in_days_of_week_in_schedule($date))
            ->addFilter(fn(Carbon $date) => $this->filter_out_excluded_dates($date));


        $seconds = 0;

        // Iterate over the period
        $current_duration->forEach(function (Carbon $day) use ($end_date_time, $start_date_time, &$seconds) {
            foreach ($this->schedules as $schedule) {

                // TODO add a start validity here

                // TODO get overlapping with the period

                foreach ($schedule->daily_periods as $period) {
                    $start_of_period = $day->copy()->setTimeFrom($period[0]);
                    $debug = $start_of_period->toString();
                    $end_of_period = $day->copy()->setTimeFrom($period[1]);
                    $debug = $end_of_period->toString();

                    $seconds += self::secondsOfOverlap(
                        CarbonPeriod::create(
                            $start_of_period,
                            $end_of_period
                        ), CarbonPeriod::create(
                            $start_date_time,
                            $end_date_time
                        )
                    );
                }
            }
        });

        return CarbonInterval::seconds($seconds);
    }

    private function secondsOfOverlap(CarbonPeriod $start_period, CarbonPeriod $end_period): int
    {
        return $start_period->overlaps($end_period)
            ? max($start_period->start, $end_period->start)->diffAsCarbonInterval(min($start_period->end, $end_period->end))->seconds
            : 0;
    }

    /**
     * Filter only the days of the week in the schedule
     * @param Carbon $date
     * @return bool
     */
    private function filter_in_days_of_week_in_schedule(Carbon $date): bool
    {
        foreach ($this->schedules as $schedule) {

            // TODO add a start validity here

            foreach ($schedule->days_of_the_week as $day) {
                if ($date->is($day)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Filter out any excluded dates
     * @param Carbon $date
     * @return bool
     */
    private function filter_out_excluded_dates(Carbon $date): bool
    {
        foreach ($this->schedules as $schedule) {

            // TODO add a start validity here

            foreach ($schedule->days_of_the_week as $day) {
                if ($date->is($day)) {
                    return true;
                }
            }
        }

        return false;
    }
}
