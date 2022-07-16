<img src="https://github.com/sifex/sla-timer/raw/HEAD/.github/assets/logo.svg?" width="50%" alt="Logo SLA Timer">


[![Latest Version on Packagist](https://img.shields.io/packagist/v/sifex/sla-timer.svg?style=flat-square)](https://packagist.org/packages/sifex/sla-timer)
[![Total Downloads](https://img.shields.io/packagist/dt/sifex/sla-timer.svg?style=flat-square)](https://packagist.org/packages/sifex/sla-timer)
![GitHub Actions](https://github.com/sifex/sla-timer/actions/workflows/main.yml/badge.svg)

> **Warning**
> This repository is currently under construction!  

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

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email alex@sinn.io instead of using the issue tracker.

## Credits

-   [Alex](https://github.com/sifex)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## PHP Package Boilerplate

This package was generated using the [PHP Package Boilerplate](https://laravelpackageboilerplate.com) by [Beyond Code](http://beyondco.de/).
