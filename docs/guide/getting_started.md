

# Getting Started

::: danger
This package is currently under construction!
:::

To get started with `sla-timer`, simply install it through composer. 



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
    SLASchedule::from('09:00:00')->to('17:30:00')->onWeekdays()
            ->andFrom('10:30:00')->to('17:30:00')->onWeekends()
            ->andFrom('17:30:00')->to('23:00:00')->on('Monday')
            ->andFrom('17:30:00')->to('10:00:00')->on(['Tuesday', 'Saturday'])
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