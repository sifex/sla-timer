# Getting Started

## Installation

::: danger
⚠️ This package is currently under construction! ⚠️
:::

You can install the `sla-timer` via composer:

```bash
composer require sifex/sla-timer
```

### Requirements

- `php` - Version 8.0 or higher

## Example Usage

To create a new SLA Timer, we can start by defining our SLA Schedule:

```php {5-6}
/**
 * Create a new SLA between 9am and 5:30pm weekdays
 */
$sla = SLA::fromSchedule(
    SLASchedule::create()->from('09:00:00')->to('17:30:00')
        ->onWeekdays()
);
```

We can define out breaches by calling the `addBreaches` method on our SLA

```php {5-6}
/**
 * Define two breaches, one at 45 minutes, and the next at 24 hours
 */
$sla->addBreaches([
    new SLABreach('First Response', '45m'),
    new SLABreach('Resolution', '24h'),
]);
```

Now that our **SLA Schedule** and **SLA Breaches** are defined, all we have to do is give our _subject_ "creation time" – or our SLA star time – to either the `status` method, or the `duration` method.

```php
// Given the time now is 2022-07-21 11:00:35 
$status = $sla->status('2022-07-21 09:00:00'); // SLAStatus
$status->breaches // [SLABreach]{1}

$duration = $sla->duration('2022-07-21 09:00:00'); // CarbonInterval
$duration->forHumans(); // 2 hours 35 seconds
```

::: info Please note:
By default, if the SLA is still running, `Carbon::now()` is used as the comparison time.
:::

::: tip Timestamp Parsing:
Any timestamp that's understood by `Carbon::parse()` can be used here!
:::

::: warning Timezone Heads up!
All times should be in UTC. If your `datetimes` are not in UTC, you can see our **[Dealing with Timezones guide here](#)**. 
::: 

## Stopping SLAs

To stop the SLA, simply pass in the date & time the SLA should be stopped into the `status` or `duration` method.

```php
// Given the time now is 2022-07-21 11:00:35 & using the SLAs defined above
$status = $sla->status('2022-07-21 08:00:00', '2022-07-21 09:35:00'); // SLAStatus
$status->breaches // []

$duration = $sla->duration('2022-07-21 08:00:00', '2022-07-21 09:35:00'); // CarbonInterval
$duration->forHumans(); // 35 min
```


## Setting up Pause Periods

Pause periods & holidays operate very similarly. So set up either, head over to [Holidays & Skipped Days](/guide/holidays)

