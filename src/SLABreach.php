<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class SLABreach
{
    public CarbonInterval $breached_after;

    public bool $breached = false;

    public function __construct($string_duration)
    {
        $this->breached_after = CarbonInterval::fromString($string_duration);
    }

    public function test(CarbonInterval $current_interval)
    {
        $this->breached = $current_interval->cascade()->totalSeconds > $this->breached_after->cascade()->totalSeconds;
    }
}
