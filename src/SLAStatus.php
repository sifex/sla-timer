<?php

namespace Sifex\SlaTimer;

use Carbon\CarbonInterval;

class SLAStatus
{
    /** @var SLABreach[]  */
    public array $breaches = [];

    /** @var CarbonInterval */
    public CarbonInterval $interval;
}
