<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo for SLA Timer">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat-square)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat-square)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

> **Warning**
> This repository is currently under construction!  

<a href="https://twitter.com/sifex/status/1548374115815346178">
<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/hiring.svg?" height="49" alt="Logo for SLA Timer">
</a>

A PHP package for calculating & tracking the Service Level Agreement completion timings.

## Installation

You can install the package via composer:

```bash
composer require sifex/sla-timer
```

## Example Usage

```php
$ServiceLevelAgreement = new SLA(
        (new SLASchedule([
            ['09:00:00', '17:30:00'],
            ['09:00:00', '09:30:00'], // Any overlaps are ignored
        ]))->onWeekdays()
    );

// Give the SLA a date from
$ServiceLevelAgreement->calculate('Monday, 11-July-22 08:59:00'); // Returns a CarbonInterval  
```

### Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Frequently Asked Questions

## About

This repository came together after I set myself the challenge to write the proof-of-concept in under 2 hours. After realising the concept of _time_ is one hell of a beast to tackle (especially timezones, [see Tom Scott's video on time-zones](https://www.youtube.com/watch?v=-5wpm-gesOY) for more information), I will end up finishing it in under 24h. 

## Credits

-   [Alex](https://github.com/sifex)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.