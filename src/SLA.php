<?php

namespace Sifex\SlaTimer;

use Carbon\Carbon;
use Carbon\CarbonInterval as CarbonDuration;
use Carbon\CarbonPeriod;

class SLA
{
    private const INITIAL_START_TIME = '1970-01-01 00:00:01';

    private $slas = [

    ];


    public function __construct($definition, $start_time = self::INITIAL_START_TIME)
    {
        $this->defineSLA($definition);
    }

    /**
     * @param $definition
     * @param string|Carbon $start_time
     */
    public function defineSLA($definition, $start_time = self::INITIAL_START_TIME)
    {
        if(! gettype($start_time) === 'string') {
            $start_time = Carbon::parse($start_time);
        }

        // 0
        $initial_duration = CarbonDuration::fromString('0 seconds');

        $start_of_day = '9am';
        $end_of_day = '5pm';


        $startTime = Carbon::create(2022, 6, 10, 8, 20, 0);
        $endTime = Carbon::now();

        $duration = $startTime->diffInSeconds($endTime, true);



    }

    /**
     * ––––– Schedule –––––
     * Start On
     * Stop On
     *
     * Versioning (ie. one SLA before this date, and one after this date)
     * TimeZone
     *
     */

    /**
     * Returns
     * @return CarbonInterval
     */
    public function calculate(): CarbonInterval
    {

    }
}
