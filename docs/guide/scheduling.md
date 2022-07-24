# Building Schedules

`sla-timer` gives us a pretty flexible Schedule builder to start building your SLA Schedules with. 

By default, if you don't specify what days you want your SLA to be run on, the schedule defaults to **Every Day**. 

```php {2-5}
$sla = SLA::fromSchedule(
    SLASchedule::create()
        ->from('09:00:00')->to('17:00:00')->onWeekdays()->and()
        ->from('10:00:00')->to('16:00:00')->onWeekends()->and()
        ->from('23:00:00')->to('23:30:00')->everyDay()
);
```
