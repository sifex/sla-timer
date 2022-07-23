<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use ReflectionException;
use Sifex\SlaTimer\Trai\IsAnAgenda;

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

    public function addBreach(SLABreach $breach): SLA
    {
        $this->breach_definitions[] = $breach;

        return $this;
    }

    public function addBreaches(...$breaches): SLA
    {
        collect($breaches)->flatten()->each(fn ($b) => $this->addBreach($b));

        return $this;
    }

    public function addSchedule(SLASchedule $definition): self
    {
        $this->schedules[] = $definition;

        return $this;
    }

    public static function fromSchedule(SLASchedule $definition): SLA
    {
        return new self($definition);
    }

    private function calculate($subject_start_time, $subject_stop_time = null): SLAStatus
    {
        $main_target_period = $this->get_current_duration(
            // When the SLA started
            Carbon::parse($subject_start_time),

            // From where we want to stop counting the SLA up to
            // TODO Customise the stop time
            $subject_stop_time ?? Carbon::now()
        );

        // Iterate over the period
        $interval = collect($main_target_period)->map(function (Carbon $daily_subject_period) use ($main_target_period) {
            /**
             * After we've divided each day, find where the start and end times are by min/max'ing them
             */
            $start_of_day = max($main_target_period->start->clone(), $daily_subject_period->clone());
            $end_of_day = min($main_target_period->end->clone(), $daily_subject_period->clone()->addHours(24)->subSecond());

            /**
             * Create a 24h period
             */
            $daily_period = CarbonPeriod::create($start_of_day, $end_of_day)
                ->setDateInterval(CarbonInterval::seconds());

            /**
             * Grab the enabled schedule, compare this every day to see if we now have a schedule that would
             * supersede it.
             */
            $enabled_schedule = $this->get_enabled_schedule_for_day();

            /**
             * Deduplicate our SLA Periods
             * Why do this here? Mostly because of superseded schedules...
             */
            $sla_periods = collect($enabled_schedule->agendas)
                ->flatMap(fn (IsAnAgenda $a) => $a->toPeriods($main_target_period))
                ->reduce(function ($carry, CarbonPeriod $p) {
                    return count($carry) ? [...$p->diff(...$carry), ...$carry] : [$p];
                }, []);

            /**
             * Grab all the overlapping periods from our daily agenda
             */
            if ($sla_periods) {
                $sla_coverage_area = $daily_period->overlapAny(
                    ...$sla_periods
                );
            } else {
                $sla_coverage_area = [];
            }

            /**
             * Get the interval of each overlapping period and place it into an array of intervals
             */
            /** @var CarbonInterval[] $intervals */
            $intervals = collect($sla_coverage_area)
                ->map(fn (CarbonPeriod $carbonPeriod): CarbonInterval => self::calculate_interval($carbonPeriod))
                ->toArray();

            return self::combine_intervals($intervals);

            /**
             * Then combine all intervals
             */
        })->pipe(fn ($c) => self::combine_intervals($c->toArray()));

        return new SLAStatus(
            collect($this->breach_definitions)
                ->each(fn (SLABreach $b) => $b->check($interval))
                ->filter(fn (SLABreach $b) => $b->breached)
                ->toArray(),
            $interval
        );
    }

    public function status(string $started_at, string $stopped_at = null): SLAStatus
    {
        return $this->calculate($started_at, $stopped_at);
    }

    public function duration(string $started_at, string $stopped_at = null): CarbonInterval
    {
        return $this->calculate($started_at, $stopped_at)->interval;
    }

    /**
     * @param  string  $started_at
     * @return SLABreach[]
     */
    public function breaches(string $started_at): array
    {
        return $this->calculate($started_at)->breaches;
    }

    /**
     * Gets the current subject duration, sets the interval to 1d and filters out anything we don't want
     *
     * @param $subject_start_time
     * @param $end_date_time
     * @return CarbonPeriod
     */
    private function get_current_duration($subject_start_time, $end_date_time): CarbonPeriod
    {
        return CarbonPeriod::create($subject_start_time, $end_date_time)
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(fn (Carbon $date) => self::filter_out_excluded_dates($date));
    }

    /**
     * Gets the enabled schedule for any given day
     *
     * @return SLASchedule
     */
    private function get_enabled_schedule_for_day(): SLASchedule
    {
        return collect($this->schedules)
            ->filter(function ($schedule) {
                return true; // TODO Filter out based on `effective_date`
            })
            ->last();
    }

    /**
     * Turns a single period into an interval
     *
     * @param $period
     * @return CarbonInterval
     */
    private static function calculate_interval($period): CarbonInterval
    {
        return CarbonInterval::seconds($period->end->unix() - $period->start->unix());
    }

    /**
     * Combines two different intervals
     *
     * @param  array  $intervals
     * @return CarbonInterval
     */
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
    private static function filter_out_excluded_dates(Carbon $date): bool
    {
        return true;
    }
}
