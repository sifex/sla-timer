<?php

namespace Sifex\SlaTimer\Traits;

use Sifex\SlaTimer\SLA;
use Sifex\SlaTimer\SLAProgramme;
use Sifex\SlaTimer\SLASchedule;

trait HasTimezone
{
    public string $timezone = 'UTC';

    public function setTimezone(string $timezone): SLASchedule
    {
        $this->timezone = $timezone;

        return $this;
    }
}
