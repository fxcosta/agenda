<?php namespace Agenda\Data;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class TimeRangeInterval extends TimeRange {

    /**
     * Range interval
     *
     * @var Carbon\CarbonInterval
     */
    protected $interval;

    /**
     * Padding interval the interval between the end of range and
     * the start of next interval
     *
     * @var Carbon\CarbonInterval
     */
    protected $paddingInterval;

    /**
     * Make new TimeRangeInterval instance
     *
     * @param Carbon\Carbon $startTime
     * @param Carbon\Carbon $endTime
     * @param Carbon\CarbonInterval $interval
     * @param Carbon\CarbonInterval|null $paddingInterval
     * @return void
     * @throws LogicException
     */
    public function __construct(
        Carbon $startTime,
        Carbon $endTime,
        CarbonInterval $interval,
        CarbonInterval $paddingInterval = null
    ) {
        parent::__construct($startTime, $endTime);

        // Ensure immutable intervals

        $this->interval = CarbonInterval::instance($interval);

        if ( ! is_null($paddingInterval)) {
            $this->paddingInterval = CarbonInterval::instance($paddingInterval);
        }
    }

    /**
     * Make new TimeRangeInterval instance using only startTime and interval
     * calculate the end time
     *
     * @param Carbon\Carbon $startTime
     * @param Carbon\CarbonInterval $interval
     * @param Carbon\CarbonInterval|null $paddingInterval
     * @return Agenda\Data\TimeRangeInterval
     */
    public static function range(
        Carbon $startTime,
        CarbonInterval $interval,
        CarbonInterval $paddingInterval = null
    ) {
        $endTime = $startTime
            ->copy()
            ->add($interval);
        return new static($startTime, $endTime, $interval, $paddingInterval);
    }

    /**
     * Get interval
     *
     * @return Carbon\CarbonInterval
     */
    public function getInterval()
    {
        return CarbonInterval::instance($this->interval);
    }

    /**
     * Get padding interval
     *
     * @return Carbon\CarbonInterval|null
     */
    public function getPaddingInterval()
    {
        if ( ! is_null($this->paddingInterval)) {
            return CarbonInterval::instance($this->paddingInterval);
        }
    }

    /**
     * Make new TimeRangeInterval instance of next time range
     *
     * @return Agenda\Data\TimeRangeInterval
     */
    public function next()
    {
        $newStartTime = $this->endTime
            ->copy();

        // Add padding time
        if ( ! is_null($this->paddingInterval)) {
            $newStartTime->add($this->paddingInterval);
        }

        return static::range($newStartTime, $this->interval, $this->paddingInterval);
    }

    /**
     * Make new TimeRangeInterval instance of previous time range
     *
     * @return Agenda\Data\TimeRangeInterval
     */
    public function prev()
    {
        $newStartTime = $this->startTime
            ->copy()
            ->sub($this->interval);

        // Sub padding time
        if ( ! is_null($this->paddingInterval)) {
            $newStartTime->sub($this->paddingInterval);
        }

        return static::range($newStartTime, $this->interval, $this->paddingInterval);
    }

    /**
     * Make new TimeRangeInterval instance with new start time
     *
     * @param Carbon\Carbon $startTime
     * @return Agenda\Data\TimeRangeInterval
     */
    public function timeRangeInterval(Carbon $startTime)
    {
        return static::range($startTime, $this->interval, $this->paddingInterval);
    }

    /**
     * Instance the corresponding range
     *
     * @return Agenda\Data\TimeRange
     */
    public function timeRange()
    {
        return new TimeRange($this->startTime, $this->endTime);
    }
}

