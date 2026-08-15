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

## Overnight Schedules

Schedules that cross midnight are supported. A window whose end time is earlier than its start time
carries over into the next day:

```php
// Covers 22:00 -> midnight, then midnight -> 02:00 the next day
SLASchedule::create()->from('22:00:00')->to('02:00:00')->everyDay()
```

A full 24 hour day can be expressed by using the same time for `from` and `to`:

```php
// 24/7 coverage
SLASchedule::create()->from('09:00:00')->to('09:00:00')->everyDay()
```

