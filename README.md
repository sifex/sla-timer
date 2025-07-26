<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo for SLA Timer">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

A PHP package for calculating & tracking the Service Level Agreement completion timings.

### Features

- 🕚 Easy schedule building
- ‼️ Defined breaches
- 🏝 Holiday & Paused Durations

---
## Installation

You can install the `sla-timer` via composer:

```bash
composer require sifex/sla-timer
```

## Getting Started

The best place to get started with SLA timer is to head over to the [✨ SLA Timer Getting Started Documentation](https://sifex.github.io/sla-timer/guide/getting_started). 

## Example Usage

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

```php
/**
 * Define two breaches, one at 24 hours, and the next at 100 hours
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
$duration->forHumans(); // 1 day 15 hours
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

-   [Alex](https://github.com/sifex)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.