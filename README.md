<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo for SLA Timer">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat&labelColor=2c353c)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

> **Warning**
> This repository is currently under construction!  


A PHP package for calculating & tracking the Service Level Agreement completion timings.

### Features

- Daily scheduling
- Per-day scheduling
- 

<a href="https://twitter.com/sifex/status/1548374115815346178">
<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/hiring.svg?" height="49" alt="Logo for SLA Timer">
</a>


## Installation

You can install the `sla-timer` via composer:

```bash
composer require sifex/sla-timer
```

## Example Usage

```php
/**
 * Create a new SLA between 9am and 5:30pm weekdays
 */
$sla = new SLA(
    SLASchedule::from([
        ['09:00:00', '17:30:00']
    ])->onWeekdays()->andFrom([
        ['10:30:00', '17:30:00']
    ])->onWeekends();
);

// Calculate any SLA given a start time
$ServiceLevelAgreement->calculate('11-July-22 08:59:00'); // CarbonInterval  
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