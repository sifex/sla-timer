# Updating Schedules

Because sometimes we might need to update our SLA Schedules over time – without invalidating existing SLA
calculations, `sla-timer` allows us to update the `SLA` with a new `SLASchedule` that will commence after
the `effectiveFrom` date provided.

```php {9-11}
// Create our initial schedule
$sla = SLA::fromSchedule(
    SLASchedule::create()->from('09:00:00')->to('17:30:00')
        ->everyDay()
);

// Add our new superseded schedule, only starting after 26th July 2022
$sla->addSchedule(
    SLASchedule::create()->effectiveFrom('26-07-2022')
        ->from('09:00:00')->to('17:30:00')->onWeekdays()
        ->andFrom('10:00:00')->to('16:00:00')->onWeekends()
);
```

Then if we run our duration method across both our old schedule and our new schedule.

```php
// Given the time now is 14:00:00 29-07-2022
$sla->duration('05:35:40 25-07-2022')->totalHours; // 34
```

<table>
    <thead>
        <tr>
            <th>Tables</th>
            <th class="text-right">Old Schedule</th>
            <th class="text-right">New Schedule</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="font-bold bg-neutral-700">25th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">30s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">26th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">30s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">27th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">30s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">28th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">30s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">29th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">30s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">30th July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">10s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">31st July</td>
            <td class="text-right bg-emerald-600">60s</td>
            <td class="text-right">10s</td>
        </tr>
        <tr>
            <td class="font-bold bg-neutral-700">Total</td>
            <td class="text-right">420s</td>
            <td class="text-right">170s</td>
        </tr>
    </tbody>
</table>