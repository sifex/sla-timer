# Updating Schedules

Because sometimes we might need to update our SLA Schedules over time – without invalidating existing SLA
calculations, `sla-timer` allows us to update the `SLA` with a new `SLASchedule` that will commence after
the `effectiveFrom` date provided.

```php {9-11}
// Create our initial schedule
$sla = SLA::fromSchedule(
    SLASchedule::create()->from('09:00:00')->to('09:01:00')
        ->everyDay()
);

// Add our new superseded schedule, only starting after 27 July 2022
$sla->addSchedule(
    SLASchedule::create()->effectiveFrom('27-07-2022')
        ->from('09:00:00')->to('09:00:30')->onWeekdays()->and()
        ->from('09:00:00')->to('09:00:10')->onWeekends()
);
```

Then if we run our duration method across both our old schedule and our new schedule.

```php
// Given the time now is 14:00:00 31-07-2022
$sla->duration('08:35:40 25-07-2022')->totalSeconds; // 230
```

<table class="w-full">
    <thead>
        <tr>
            <th>Tables</th>
            <th class="text-right">Old Schedule</th>
            <th class="text-right">New Schedule</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">25<sup>th</sup> July</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">60s</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">30s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">26<sup>th</sup> July</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">60s</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">30s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">27<sup>th</sup> July</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">60s</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">28<sup>th</sup> July</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">60s</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">29<sup>th</sup> July</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">60s</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">30s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">30<sup>th</sup> July</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">60s</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">10s</td>
        </tr>
        <tr>
            <td class="font-bold dark:bg-neutral-900 bg-neutral-100">31<sup>st</sup> July</td>
            <td class="text-right text-neutral-400 dark:text-neutral-500">60s</td>
            <td class="text-right font-semibold dark:bg-emerald-600/40 bg-emerald-300/30">10s</td>
        </tr>
        <tr>
            <td rowspan="2" class="font-bold dark:bg-neutral-900 bg-neutral-200">Total</td>
            <td class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">120s</td>
            <td class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">110s</td>
        </tr>
        <tr>
            <td colspan="2" class="text-right font-bold dark:bg-neutral-900 bg-neutral-200">230s</td>
        </tr>
    </tbody>
</table>