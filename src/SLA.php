<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

class SLA
{
    /** @var SLASchedule[]  */
    private array $schedules = [];


    public function __construct(SLASchedule $schedule)
    {
        $this->defineSLA($schedule);
    }

    public function defineSLA(SLASchedule $definition)
    {
        $this->schedules[] = $definition;
    }

    /**
     * ––––– Schedule –––––
     * Start On
     * Stop On
     *
     * Versioning (ie. one SLA before this date, and one after this date)
     * TimeZone
     *
     */

    public function calculate($start_date_time)
    {
        // When the SLA started
        $start_date_time = Carbon::parse($start_date_time);

        // From where we want to stop counting the SLA up to
        $end_date_time = Carbon::now();

        // Grab every day between the two dates
        $period = CarbonPeriod::create($start_date_time, $end_date_time)
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(function(Carbon $date) {
                // Filter only the days of the week in the schedule

                foreach ($this->schedules as $schedule) {

                    // TODO add a start validity here

                    foreach ($schedule->days_of_the_week as $day) {
                        if($date->is($day)) {
                            return true;
                        }
                    }
                }
                return false;
            })
            ->addFilter(function(Carbon $date) {
                // Filter out any excluded dates

                foreach ($this->schedules as $schedule) {

                    // TODO add a start validity here

                    foreach ($schedule->excluded_dates as $excluded_day) {
                        if($date->is($excluded_day[0])) {
                            return false;
                        }
                    }
                }
                return true;
            });

        $seconds = 0;

        // Iterate over the period
        $period->forEach(function(Carbon $day) use ($period, $end_date_time, $start_date_time, &$seconds) {
            foreach ($this->schedules as $schedule) {

                // TODO add a start validity here

                // TODO get overlapping with the period

                
                foreach ($schedule->daily_periods as $period) {
                    $start_of_period = max(
                        $day->copy()->setTimeFrom($period[0]),
                        $start_date_time,
                    );

                    $end_of_period = min(
                        $day->copy()->setTimeFrom($period[1]),
                        $end_date_time
                    );

                    $seconds += $start_of_period->diffInSeconds(
                        $end_of_period
                    );
                }
            }
        });

        return CarbonInterval::seconds($seconds);
    }
}
