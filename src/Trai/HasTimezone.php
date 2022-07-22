<?php

namespace Sifex\SlaTimer\Trai;

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
