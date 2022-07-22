<?php

namespace Sifex\SlaTimer\Trai;

use Carbon\CarbonPeriod;

interface IsAnAgenda
{
    /**
     * @param  CarbonPeriod  $subject_period
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array;
}
