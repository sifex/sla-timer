<?php

namespace Sifex\SlaTimer;

use Carbon\CarbonInterval;

class SLABreach
{
    public string $name = 'Generic Breach';

    public CarbonInterval $breached_after;

    public bool $breached = false;

    public function __construct(string $name, string $string_duration)
    {
        $this->name = $name;
        $this->breached_after = CarbonInterval::fromString($string_duration);
    }

    public function check(CarbonInterval $current_interval)
    {
        $this->breached = $current_interval->cascade()->totalSeconds > $this->breached_after->cascade()->totalSeconds;
    }
}
