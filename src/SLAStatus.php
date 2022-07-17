<?php

namespace Sifex\SlaTimer;

use Carbon\CarbonInterval;

class SLAStatus
{
    /** @var SLABreach[] */
    public array $breaches = [];

    /** @var CarbonInterval */
    public CarbonInterval $interval;

    public function __construct($breaches, $interval)
    {
        $this->breaches = $breaches;
        $this->interval = $interval;
    }
}
