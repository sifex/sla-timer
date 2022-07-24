<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo for SLA Timer">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

<a href="https://twitter.com/sifex/status/1548374115815346178">
<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/hiring.svg?" alt="Hi, I'm Alex & I'm currently looking for a Laravel job. Please reach out to me via twitter, or click this link." height="49">
</a>

> **Warning**
> This repository is currently under construction!  


A PHP package for calculating & tracking the Service Level Agreement completion timings.

### Features

- 🕚 Daily & Per-Day scheduling
- ‼️ Defined breaches
- 🏝 Holiday & Paused Durations


<img src="https://github.com/sifex/sla-timer/raw/HEAD/docs/public/images/sla_basic_dark.svg#gh-dark-mode-only" alt="SLA Explanation" width="830">
<img src="https://github.com/sifex/sla-timer/raw/HEAD/docs/public/images/sla_basic_light.svg#gh-light-mode-only" alt="SLA Explanation" width="830">

## Installation

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

## Documentation

You can check out the documentation here on the [SLA Timer docs page](https://sifex.github.io/sla-timer).

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Frequently Asked Questions

// TODO

## Credits

-   [Alex](https://github.com/sifex)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.