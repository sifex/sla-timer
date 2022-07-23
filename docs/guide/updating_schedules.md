# Updating Schedules

Because sometimes we might need to update our SLA Schedules over time – without invalidating existing SLA calculations, `sla-timer` allows us to update the `SLA` with a new `SLASchedule` that will commence after the `effectiveFrom` date provided.

```php {9-11}
// Create our initial schedule
$sla = SLA::fromSchedule(
    SLASchedule::create()->from('09:00:00')->to('17:30:00')
        ->everyDay()
);

// Add our new superseded schedule, only starting after 6th July 2022
$sla->addSchedule(
    SLASchedule::create()->effectiveFrom('2022-07-06')
        ->from('09:00:00')->to('17:30:00')->onWeekdays()
        ->andFrom('10:00:00')->to('16:00:00')->onWeekends()
)
```