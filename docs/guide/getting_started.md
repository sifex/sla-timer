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

::: tip 💡 About SLAs
Before we dive in, ensure you understand what SLAs & SLA Timers are by exploring [About&nbsp;SLAs&nbsp;›](/guide/about)
:::


Let's set up an SLA timer similar to the schedule shown below.<br />For this example, we're working in the week of 25th - 29th July 2022, but the library works over many weeks

<script setup>
import { withBase } from 'vitepress';
</script>

<a :href="withBase('/images/sla_desc_light.svg')" class="lg:-mx-8 xl:-mx-16 my-8 lg:my-16 block">
    <img :src="withBase('/images/sla_desc_dark.svg')" alt="SLA Diagram – Showing the periods of approx. 9am to 5pm covered each week day " class="w-full hidden dark:block">
    <img :src="withBase('/images/sla_desc_light.svg')" alt="SLA Diagram – Showing the periods of approx. 9am to 5pm covered each week day " class="w-full dark:hidden">
</a>

To create a new SLA Timer, we can start by defining our SLA Schedule:

```php
require 'vendor/autoload.php';

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLABreach;
use Sifex\SlaTimer\SLASchedule;

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
    new SLABreach('First Response', '24h'),
    new SLABreach('Resolution', '100h'),
]);
```

Now that our **SLA Schedule** and **SLA Breaches** are defined, all we have to do is give our _subject_ "creation time" – or our SLA start time – to either the `status` method, or the `duration` method.

```php
// Given the time now is 14:00:00 29-07-2022
$status = $sla->status('05:35:40 25-07-2022'); // SLAStatus
$status->breaches; // [SLABreach] [0: { First Response } ]

$duration = $sla->duration('05:35:40 25-07-2022'); // CarbonInterval
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

