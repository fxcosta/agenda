<?php namespace Agenda\Data;

use Carbon\Carbon;
use LogicException;

class TimeRange {

    /**
     * Start time of range
     *
     * @var Carbon\Carbon
     */
    protected $startTime;

    /**
     * End time of range
     *
     * @var Carbon\Carbon
     */
    protected $endTime;

    /**
     * Make new TimeRange instance
     *
     * @param Carbon\Carbon $startTime
     * @param Carbon\Carbon $endTime
     * @return void
     * @throws LogicException
     */
    public function __construct(Carbon $startTime, Carbon $endTime)
    {
        // End time must me be greater then start time
        if ( ! $endTime->gt($startTime)) {
            throw new LogicException('End time must be greater then start time');
        }

        // Ensure that start, end to be immutable
        $this->startTime = $startTime->copy();
        $this->endTime = $endTime->copy();
    }

    /**
     * Get start time
     *
     * @return Carbon\Carbon
     */
    public function getStartTime()
    {
        return $this->startTime->copy();
    }

    /**
     * Get end time
     *
     * @return Carbon\Carbon
     */
    public function getEndTime()
    {
        return $this->endTime->copy();
    }

    /**
     * Check if end start of range are is the same day
     *
     * @return boolean
     */
    public function isSameDay()
    {
        return $this->startTime->isSameDay($this->endTime);
    }

    /**
     * Check if time range equal to given time range
     *
     * @var Agenda\Data\TimeRange $timeRange
     * @return boolean
     */
    public function equal(TimeRange $range)
    {
        return
            $this->startTime->eq($range->getStartTime()) &&
            $this->endTime->eq($range->getEndTime());
    }

    /**
     * Check if time range contains given time range
     *
     * @var Agenda\Data\TimeRange $timeRange
     * @return boolean
     */
    public function contains(TimeRange $timeRange)
    {
        return
            $this->startTime->lte($timeRange->getStartTime()) &&
            $this->endTime->gte($timeRange->getEndTime());
    }

    /**
     * Check if time range overlap given time range
     *
     * @var Agenda\Data\TimeRange $timeRange
     * @return boolean
     */
    public function overlap(TimeRange $timeRange)
    {
        return
            $this->startTime->lt($timeRange->getEndTime()) &&
            $this->endTime->gt($timeRange->getStartTime());
    }

    /**
     * Make new TimeRange instance with same time but new date
     *
     * @var Carbon\Carbon $date
     * @return Agenda\Data\TimeRange
     */
    public function rangeWithDate(Carbon $date)
    {
        $newStartTime = $this
            ->getStartTime()
            ->setDate($date->year, $date->month, $date->day);
        $newEndTime = $this
            ->getEndTime()
            ->setDate($date->year, $date->month, $date->day);

        return new static($newStartTime, $newEndTime);
    }

    /**
     * Make new TimeRange instance with same time but new dates
     *
     * @var Carbon\Carbon $startDate
     * @var Carbon\Carbon $endDate
     * @return Agenda\Data\TimeRange
     * @throws LogicException
     */
    public function rangeWithDates(Carbon $startDate, Carbon $endDate)
    {
        $newStartTime = $this
            ->getStartTime()
            ->setDate($startDate->year, $startDate->month, $startDate->day);
        $newEndTime = $this
            ->getEndTime()
            ->setDate($endDate->year, $endDate->month, $endDate->day);

        return new static($newStartTime, $newEndTime);
    }

    /**
     * Return the date time string of start, end time
     *
     * @return string
     */
    public function __toString()
    {
        $startTimeStr = $this->startTime->toDateTimeString();
        $endTimeStr = $this->endTime->toDateTimeString();

        return "<br />
            Start ~> $startTimeStr <br />
            End ~> $endTimeStr <br />
        ";
    }

    /**
     * Json serialize
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        $startTimeStr = $this->startTime->toDateTimeString();
        $endTimeStr = $this->endTime->toDateTimeString();

        return array(
            'start' => $startTimeStr,
            'end'   => $endTimeStr
        );
    }
}

