# Working with Holidays

The best way to work with Holidays is to use the [Yasumi Holiday PHP Library](https://www.yasumi.dev) (https://yasumi.dev).

Simply require the library using composer:

```bash
$ composer require azuyalabs/yasumi
```

Then iterate over each holiday and add it to the SLA's holiday periods.

```php
require 'vendor/autoload.php';

use Yasumi\Yasumi;
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

$holidays = Yasumi::create('Australia', 2022);

// Add each holiday in 2022 to the SLA's list of Holidays
$sla->addHolidays($holidays->getHolidayDates());


```