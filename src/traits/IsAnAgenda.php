<?php

namespace Sifex\SlaTimer\Traits;

use Carbon\CarbonPeriod;

interface IsAnAgenda
{
    /**
     * @param  CarbonPeriod  $subject_period
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array;
}
