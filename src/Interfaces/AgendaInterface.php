<?php

namespace Sifex\SlaTimer\Interfaces;

use Carbon\CarbonPeriod;

interface AgendaInterface
{
    /**
     * @return CarbonPeriod[]
     */
    public function toPeriods(CarbonPeriod $subject_period): array;
}
