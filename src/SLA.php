<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use ReflectionException;
use Sifex\SlaTimer\Interfaces\AgendaInterface;

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
     * @param  SLASchedule|array<int, SLASchedule>  $schedules
     *
     * @throws ReflectionException
     */
    public function __construct(SLASchedule|array $schedules)
    {
        CarbonPeriod::mixin(EnhancedPeriod::class);

        collect([$schedules])->flatten(2)->each(function ($b) {
            $this->addSchedule($b);
        })->whenEmpty(function () {
            $this->addSchedule(SLASchedule::create());
        });
    }

    public function addBreach(SLABreach $breach): self
    {
        $this->breach_definitions[] = $breach;

        return $this;
    }

    /**
     * @param  SLABreach|array<int, SLABreach>  ...$breaches
     */
    public function addBreaches(...$breaches): self
    {
        collect([$breaches])->flatten(2)->each(fn ($b) => $this->addBreach($b));

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

    /**
     * @param  string|array<int, string>  $dates
     */
    public function addHolidays($dates): self
    {
        collect([$dates])->flatten(2)->each(fn ($d) => $this->addHoliday($d));

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

    /**
     * @param  SLASchedule|array<int, SLASchedule>  $definition
     */
    public static function fromSchedules(SLASchedule|array $definition): self
    {
        return new self($definition);
    }

    public function status(string $started_at, ?string $stopped_at = null): SLAStatus
    {
        return $this->calculate($started_at, $stopped_at);
    }

    public function duration(string $started_at, ?string $stopped_at = null): CarbonInterval
    {
        return $this->calculate($started_at, $stopped_at)->interval;
    }

    /**
     * @return SLABreach[]
     */
    public function breaches(string $started_at): array
    {
        return $this->calculate($started_at)->breaches;
    }

    private function calculate(string $subject_start_time, ?string $subject_stop_time = null): SLAStatus
    {
        $subject_start = Carbon::parse($subject_start_time);
        $subject_end = Carbon::parse($subject_stop_time ?? Carbon::now());

        $main_target_period = $this->get_current_duration($subject_start, $subject_end);

        // TODO End period should just be up until the next schedule is made
        $sla_periods = $this->recalculate_sla_periods($subject_start, $subject_end);

        // Iterate over the period
        $interval = collect(iterator_to_array($main_target_period))->map(function (Carbon $daily_subject_period) use ($subject_start, $subject_end, &$sla_periods) {
            /**
             * After we've divided each day, find where the start and end times are by min/max'ing them
             */
            $start_of_day = max($subject_start->clone(), $daily_subject_period->clone());
            $end_of_day = min($subject_end->clone(), $daily_subject_period->clone()->addHours(24));

            /**
             * Create a 24h period
             */
            $daily_period = CarbonPeriod::create($start_of_day, $end_of_day)
                ->setDateInterval(CarbonInterval::seconds());

            /**
             * Grab the enabled schedule, compare this every day to see if we now have a schedule that would
             * supersede it.
             */
            $enabled_schedule = $this->get_enabled_schedule_for_day($start_of_day);

            /**
             * Deduplicate our SLA Periods
             * Why do this here? Mostly because of superseded schedules...
             */
            if ($start_of_day->clone()->startOfDay()->unix() === Carbon::parse($enabled_schedule->valid_from)->clone()->startOfDay()->unix()) {
                $sla_periods = $this->recalculate_sla_periods($start_of_day, $subject_end);
            }

            /**
             * SLA Overlap
             * This function has been optimised
             */
            $sla_coverage_periods = collect($sla_periods)
                ->map(function (CarbonPeriod $sla_period) use ($start_of_day, $end_of_day) {
                    if ($sla_period->start === null || $sla_period->end === null) {
                        return null;
                    }

                    $e = max($sla_period->start->getTimestamp(), $start_of_day->getTimestamp());
                    $f = min($sla_period->end->getTimestamp(), $end_of_day->getTimestamp());

                    if ($e > $f) {
                        return null;
                    } // No Overlap

                    return CarbonPeriod::create(
                        Carbon::createFromTimestamp($e),
                        Carbon::createFromTimestamp($f),
                    )->setDateInterval(CarbonInterval::seconds());
                })
                ->whereNotNull()
                ->reduce(function (array $carry, ?CarbonPeriod $p) {
                    /** De-duplicate overlapping SLA periods */
                    return $p === null ? $carry : (count($carry) ? [...$p->diff(...$carry), ...$carry] : [$p]);
                }, []);

            if ($this->pause_periods) {
                $sla_coverage_periods = collect($sla_coverage_periods)->flatMap(function (CarbonPeriod $period): array {
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

    /**
     * @return CarbonPeriod[]
     */
    private function recalculate_sla_periods(CarbonInterface $from, CarbonInterface $to): array
    {
        return collect($this->get_enabled_schedule_for_day($from)->agendas)
            ->flatMap(fn (AgendaInterface $a) => $a->toPeriods(CarbonPeriod::create($from, $to)))
            ->toArray();
    }

    /**
     * Gets the current subject duration, sets the interval to 1d and filters out anything we don't want
     *
     * The period is anchored to the start of day so that the final (potentially partial) day is always
     * included in the iteration, otherwise any SLA time on the end date would be missed.
     */
    private function get_current_duration(CarbonInterface $subject_start_time, CarbonInterface $end_date_time): CarbonPeriod
    {
        return CarbonPeriod::create(
            $subject_start_time->clone()->startOfDay(),
            $end_date_time->clone()->startOfDay()
        )
            ->setDateInterval(CarbonInterval::day(1))
            ->addFilter(fn (Carbon $date) => self::filter_out_excluded_dates($date));
    }

    /**
     * Gets the enabled schedule for any given day
     */
    private function get_enabled_schedule_for_day(CarbonInterface $day): SLASchedule
    {
        return collect($this->schedules)
            ->filter(function (SLASchedule $schedule) use ($day) {
                return Carbon::parse($schedule->valid_from)->startOfDay()->getTimestamp() <= $day->getTimestamp();
            })
            ->last() ?? SLASchedule::create();
    }

    /**
     * Turns a single period into an interval
     */
    private static function calculate_interval(CarbonPeriod $period): CarbonInterval
    {
        if ($period->start === null || $period->end === null) {
            return CarbonInterval::seconds(0);
        }

        return CarbonInterval::seconds($period->end->getTimestamp() - $period->start->getTimestamp());
    }

    /**
     * Combines two different intervals
     *
     * @param  CarbonInterval[]  $intervals
     */
    private static function combine_intervals(array $intervals): CarbonInterval
    {
        return collect($intervals)
            ->reduce(function (CarbonInterval $i, CarbonInterval $overlapping_period) {
                return $i->add($overlapping_period->cascade())->cascade();
            }, CarbonInterval::seconds(0));
    }

    /**
     * Filter out any excluded dates
     */
    private static function filter_out_excluded_dates(Carbon $date): bool
    {
        return true;
    }
}
