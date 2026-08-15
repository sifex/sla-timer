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

        $pause_periods = collect($this->pause_periods)
            ->map(fn (SLAPause $pp) => $pp->toPeriod()->setDateInterval(CarbonInterval::seconds()))
            ->sortBy(fn (CarbonPeriod $p) => $p->start?->getTimestamp())
            ->values()
            ->toArray();

        $sla_cursor = 0;
        $pause_cursor = 0;

        // Iterate over the period
        $total_seconds = collect(iterator_to_array($main_target_period))->map(function (Carbon $daily_subject_period) use ($subject_start, $subject_end, &$sla_periods, &$sla_cursor, $pause_periods, &$pause_cursor) {
            /**
             * After we've divided each day, find where the start and end times are by min/max'ing them
             */
            $start_of_day = max($subject_start->clone(), $daily_subject_period->clone());
            $end_of_day = min($subject_end->clone(), $daily_subject_period->clone()->addHours(24));

            $day_start_ts = $start_of_day->getTimestamp();
            $day_end_ts = $end_of_day->getTimestamp();

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
                $sla_cursor = 0;
            }

            /**
             * Only consider SLA periods that can possibly overlap the current day, otherwise
             * the work below would scale with the entire subject duration on every day.
             *
             * The periods are generated in chronological order, so a single forward-only
             * cursor sweeps them without rescanning the whole list for every day.
             */
            $sla_count = count($sla_periods);
            while ($sla_cursor < $sla_count && ($sla_periods[$sla_cursor]->end === null || $sla_periods[$sla_cursor]->end->getTimestamp() <= $day_start_ts)) {
                $sla_cursor++;
            }

            $day_sla_periods = [];
            for ($i = $sla_cursor; $i < $sla_count; $i++) {
                $period = $sla_periods[$i];

                if ($period->start === null || $period->end === null) {
                    continue;
                }

                if ($period->start->getTimestamp() >= $day_end_ts) {
                    break;
                }

                $day_sla_periods[] = $period;
            }

            /**
             * SLA Overlap
             *
             * Clip each SLA period to the current day and merge the results into a
             * continuous union using integer timestamps, avoiding the per-day cost of
             * building CarbonPeriod objects and diffing them via spatie/period.
             */
            $coverage = [];
            foreach ($day_sla_periods as $period) {
                if ($period->start === null || $period->end === null) {
                    continue;
                }

                $start = max($period->start->getTimestamp(), $day_start_ts);
                $end = min($period->end->getTimestamp(), $day_end_ts);

                if ($start >= $end) {
                    continue;
                }

                $coverage[] = [$start, $end];
            }

            usort($coverage, fn (array $a, array $b) => $a[0] <=> $b[0]);

            $merged = [];
            foreach ($coverage as [$start, $end]) {
                if ($merged && $start <= $merged[count($merged) - 1][1]) {
                    $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $end);
                } else {
                    $merged[] = [$start, $end];
                }
            }

            /**
             * Subtract the pauses that overlap the current day, reproducing the
             * closed-interval semantics of spatie/period (the instants a pause
             * starts and ends at are consumed by it).
             *
             * The pauses are sorted by start time and swept with a forward-only
             * cursor, so only pauses that can overlap the day are considered.
             */
            if ($pause_periods) {
                $pause_count = count($pause_periods);
                while ($pause_cursor < $pause_count && ($pause_periods[$pause_cursor]->end === null || $pause_periods[$pause_cursor]->end->getTimestamp() <= $day_start_ts)) {
                    $pause_cursor++;
                }

                $day_pause_periods = [];
                for ($i = $pause_cursor; $i < $pause_count; $i++) {
                    $pause = $pause_periods[$i];

                    if ($pause->start === null || $pause->end === null) {
                        continue;
                    }

                    if ($pause->start->getTimestamp() >= $day_end_ts) {
                        break;
                    }

                    $day_pause_periods[] = $pause;
                }

                $merged = collect($merged)->flatMap(function (array $pair) use ($day_pause_periods) {
                    $parts = [$pair];

                    foreach ($day_pause_periods as $pause) {
                        $pause_start = $pause->start?->getTimestamp();
                        $pause_end = $pause->end?->getTimestamp();

                        if ($pause_start === null || $pause_end === null || $pause_end < $pair[0] || $pause_start > $pair[1]) {
                            continue;
                        }

                        $next = [];
                        foreach ($parts as [$start, $end]) {
                            if ($pause_start - 1 >= $start) {
                                $next[] = [$start, min($end, $pause_start - 1)];
                            }

                            if ($pause_end + 1 <= $end) {
                                $next[] = [max($start, $pause_end + 1), $end];
                            }
                        }

                        $parts = $next;

                        if (! $parts) {
                            break;
                        }
                    }

                    return $parts;
                })->toArray();
            }

            /**
             * Sum the seconds of every remaining coverage period of the day
             */
            $day_seconds = 0;
            foreach ($merged as [$start, $end]) {
                $day_seconds += $end - $start;
            }

            return $day_seconds;

            /**
             * Then sum the seconds of every day
             */
        })->sum();

        $interval = CarbonInterval::seconds((int) round($total_seconds))->cascade();

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
     * Filter out any excluded dates
     */
    private static function filter_out_excluded_dates(Carbon $date): bool
    {
        return true;
    }
}
