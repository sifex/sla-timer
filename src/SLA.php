<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Illuminate\Support\Collection;
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
     * Pause periods used for pausing the SLAs as well as Holidays
     *
     * @var SLAPause[]
     */
    private array $pause_periods = [];

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

    public function addBreach(SLABreach $breach): self
    {
        $this->breach_definitions[] = $breach;

        return $this;
    }

    public function addBreaches(...$breaches): self
    {
        collect($breaches)->flatten()->each(fn ($b) => $this->addBreach($b));

        return $this;
    }

    public function addPause(string $start_date, string $end_date): self
    {
        $this->pause_periods[] = new SLAPause($start_date, $end_date);

        return $this;
    }

    public function addHoliday(string $date): self
    {
        $this->pause_periods[] = new SLAHoliday($date, $date);

        return $this;
    }

    public function clearPausePeriods(): self
    {
        $this->pause_periods = [];

        return $this;
    }

    public function addHolidays(string $dates): self
    {
        collect($dates)->flatten()->each(fn ($d) => $this->addHoliday($d));

        return $this;
    }

    public function addSchedule(SLASchedule $definition): self
    {
        $this->schedules[] = $definition;

        return $this;
    }

    public static function fromSchedule(SLASchedule $definition): self
    {
        return new self($definition);
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

    private function calculate($subject_start_time, $subject_stop_time = null): SLAStatus
    {
        $main_target_period = $this->get_current_duration(
            // When the SLA started
            Carbon::parse($subject_start_time),

            // From where we want to stop counting the SLA up to

            $subject_stop_time ?? Carbon::now()
        );

        $sla_periods = $this->recalculate_sla_periods($main_target_period->start, $main_target_period->end);

        // Iterate over the period
        $interval = collect($main_target_period)->map(function (Carbon $daily_subject_period) use ($main_target_period, $sla_periods) {
            /**
             * After we've divided each day, find where the start and end times are by min/max'ing them
             */
            $start_of_day = max($main_target_period->start->clone(), $daily_subject_period->clone());
            $end_of_day = min($main_target_period->end->clone(), $daily_subject_period->clone()->addHours(24));

            /**
             * Create a 24h period
             */
            $daily_period = CarbonPeriod::create($start_of_day, $end_of_day)
                ->setDateInterval(CarbonInterval::seconds());

            /**
             * Grab the enabled schedule, compare this every day to see if we now have a schedule that would
             * supersede it. // TODO
             */
            $enabled_schedule = $this->get_enabled_schedule_for_day($daily_period);

            /**
             * Deduplicate our SLA Periods
             * Why do this here? Mostly because of superseded schedules...
             */
            if (false /* Our enabled schedule has changed today */) {
                $sla_periods = $this->recalculate_sla_periods($main_target_period);
            }

            /**
             * SLA Overlap
             * This function has been optimised
             */
            $sla_coverage_periods = collect($sla_periods)
                ->map(function(CarbonPeriod $sla_period) use ($daily_period) {
                    $e = max($sla_period->start->unix(), $daily_period->start->unix());
                    $f = min($sla_period->end->unix(), $daily_period->end->unix());

                    if($e > $f) {
                        return null;
                    }
                    return CarbonPeriod::create(
                        Carbon::parse($e),
                        Carbon::parse($f),
                    )->setDateInterval(CarbonInterval::seconds());
                })
                ->whereNotNull();

            if ($this->pause_periods) {
                $sla_coverage_periods = collect($sla_coverage_periods)->flatMap(function (CarbonPeriod $period) {
                    $pause_periods = collect($this->pause_periods)->map(fn (SLAPause $pp) => $pp->toPeriod()->setDateInterval(CarbonInterval::seconds()))->toArray();

                    return $period->diff(...$pause_periods);
                })->toArray();
            }

            /**
             * Get the interval of each overlapping period and place it into an array of intervals
             */
            /** @var CarbonInterval[] $intervals */
            $intervals = collect($sla_coverage_periods)
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

    private function recalculate_sla_periods(Carbon $from, Carbon $to): array
    {
        return collect($this->get_enabled_schedule_for_day($from)->agendas)
            ->flatMap(fn (IsAnAgenda $a) => $a->toPeriods($main_target_period))
            ->reduce(function(Collection $carry, CarbonPeriod $sla_period) {
                [$overlapping_periods, $carry] = $carry->partition(function($existing_period) use ($sla_period) {
                    $e = max($sla_period->start->unix(), $existing_period->start->unix());
                    $f = min($sla_period->end->unix(), $existing_period->end->unix());

                    if($e > $f) {
                        return false;
                    } else {
                        return true;
                    }
                });

                if($overlapping_periods->count()) {
                    return $carry->add(
                        CarbonPeriod::create(
                            collect($overlapping_periods)->add($sla_period)->map(fn(CarbonPeriod $p) => $p->start->unix())->max(),
                            collect($overlapping_periods)->add($sla_period)->map(fn(CarbonPeriod $p) => $p->end->unix())->min(),
                        )
                    );
                } else {
                    return $carry->add($sla_period);
                }
            }, collect())->toArray();
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
     * @param Carbon $day
     * @return SLASchedule
     */
    private function get_enabled_schedule_for_day(Carbon $day): SLASchedule
    {
        return collect($this->schedules)
            ->filter(function (SLASchedule $schedule) use ($day) {
                return Carbon::parse($schedule->valid_from)->unix() < $day->unix();
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
            }, CarbonInterval::seconds(0));
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
