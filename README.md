<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo for SLA Timer">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

> **Warning**
> This repository is currently under construction!  


A PHP package for calculating & tracking the Service Level Agreement completion timings.

### Features

- 🕚 Daily & Per-Day scheduling
- ‼️ Defined breaches
- 🏝 Holiday & Paused Durations

<a href="https://twitter.com/sifex/status/1548374115815346178">
<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/hiring.svg?" alt="Hi, I'm Alex & I'm currently looking for a Laravel job. Please reach out to me via twitter, or click this link." height="49">
</a>

![Basic SLA Format](https://github.com/sifex/sla-timer/raw/HEAD/docs/public/images/sla_basic_dark.svg#gh-dark-mode-only)
![Basic SLA Format](https://github.com/sifex/sla-timer/raw/HEAD/docs/public/images/sla_basic_light.svg#gh-light-mode-only)

## Installation

You can install the `sla-timer` via composer:

```bash
composer require sifex/sla-timer
```

## Example Usage

SLA Timer provides an API for you to compose your own uses [`Carbon\Carbon`](https://carbon.nesbot.com/docs) to calculate the 
To create a new SLA between 9am and 5:30pm weekdays, use the following 

```php

$sla = SLA::fromSchedule(
        SLASchedule::create()->from('09:00:00')->to('17:30:00')->everyDay()
    );

    $sla->addBreaches([
        new SLABreach('first_response', '45m'),
        new SLABreach('resolution', '24h'),
    ]);

    testTime()->freeze('2022-07-21 09:46:00');
    $status = $sla->status('2022-07-10 09:00:00'); // SLAStatus
    expect($status->breaches)->toHaveCount(2); // SLABreach[]

    $duration = $sla->duration('2022-07-21 08:59:00'); // CarbonInterval
    $duration->forHumans();
```

### Complex & Multiple Schedules

```php
/**
 * Create a new SLA between 9am and 5:30pm weekdays
 */
$sla = new SLA(
    (new SLASchedule)->from('09:00:00')->to('17:30:00')->onWeekdays()
            ->andFrom('10:30:00')->to('17:30:00')->onWeekends()
            ->andFrom('17:30:00')->to('23:00:00')->on('Monday')
            ->andFrom('17:30:00')->to('10:00:00')->on(['Tuesday', 'Saturday'])
            ->except(['25 Dec 2021'])
)

// Calculate any SLA given a start time and return a CarbonInterval
$sla->duration('11-July-22 08:59:00')->totalSeconds = 12345;
```

### Breaches

```php
/**
 * Create a new SLA between 9am and 5:30pm weekdays
 */
$sla = new SLA(
    (new SLASchedule)->from('09:00:00')->to('17:30:00')->onWeekdays()
            ->andFrom('10:30:00')->to('17:30:00')->onWeekends()
            ->andFrom('17:30:00')->to('23:00:00')->on('Monday')
            ->andFrom('17:30:00')->to('10:00:00')->on(['Tuesday', 'Saturday'])
            ->except(['25 Dec 2021'])
)->addBreaches([
    SLABreach('first_response', '45m'),
    SLABreach('resolution', '1d 2h'),
]);

$status = $sla->status('11-July-22 08:59:00')->breaches;

// Calculate any SLA given a start time and return a CarbonInterval
$sla->duration('11-July-22 08:59:00')->totalSeconds = 12345;
```

### Superseding old schedules

```php
/**
 * Create a new schedule effective from 6th July 2022 (that supersedes the old one)
 */
$sla->addNewSchedule(
    SLASchedule::effectiveFrom('2022-07-06')->from([
        ['09:00:00', '17:30:00']
    ])->everyDay();
);

```

### Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Frequently Asked Questions

## About

This repository came together after I set myself the challenge to write the proof-of-concept in under 2 hours. After realising the concept of _time_ is one hell of a beast to tackle (especially timezones, [see Tom Scott's video on time-zones](https://www.youtube.com/watch?v=-5wpm-gesOY) for more information), I will end up finishing it in under 48h. 

## Credits

-   [Alex](https://github.com/sifex)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.