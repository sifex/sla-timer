# Pausing SLAs

Pausing generates periods of time we don't want to count towards our SLA Timer.

<script setup>
import { withBase } from 'vitepress';
</script>

Pausing works retroactively to the `status` or `duration` method and is required to be defined before we calculate the full SLA.


<a :href="withBase('/images/sla_pause_light.svg')" class="lg:-mx-16 my-16 lg:my-24 xl:my-32 block !mb-0">
    <img :src="withBase('/images/sla_pause_dark.svg')" alt="SLA Pausing Diagram – Showing the periods of approx. 9am to 5pm covered each week day, but with a paused period on Wednesday" class="w-full hidden! dark:block! !mt-0">
    <img :src="withBase('/images/sla_pause_light.svg')" alt="SLA Pausing Diagram – Showing the periods of approx. 9am to 5pm covered each week day, but with a paused period on Wednesday" class="w-full dark:hidden!">
</a>

```php
// Given the time now is 14:00:00 29-07-2022

$sla = SLA::fromSchedule(
    SLASchedule::create()->from('09:00:00')->to('17:30:00')
);

$sla->addPause('03:00:00 27-07-2022', '13:59:59 27-07-2022');

$sla->duration('05:35:40 25-07-2022')->totalHours; // 34
```

::: info Note:
If you want to specify a full day of pausing, you might want to [setup Holidays instead](/guide/holidays).
:::