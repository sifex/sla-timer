<?php

namespace Sifex\SlaTimer;

use Carbon\CarbonInterval;

class SLAStatus
{
    /** @var SLABreach[] */
    public array $breaches = [];

    public CarbonInterval $interval;

    public function __construct($breaches, $interval)
    {
        $this->breaches = $breaches;
        $this->interval = $interval;
    }

    public function hasABreach(): bool
    {
        return collect($this->breaches)->contains(fn (SLABreach $b) => $b->breached);
    }
}
