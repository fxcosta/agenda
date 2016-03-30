<?php namespace Agenda\Data;

use Carbon\Carbon;
use Agenda\Util\Arrays;
use LogicException;

class BookableTimeRange extends TimeRange {

    /**
     * Availables workstation ids for booking
     * null when workstation are not handled
     *
     * @var array|null
     */
    protected $workstationIds;

    /**
     * Make new BookableTimeRange instance
     *
     * @param Carbon\Carbon $startTime
     * @param Carbon\Carbon $endTime
     * @param array|null $workstationIds
     * @return void
     * @throws LogicException
     */
    public function __construct(Carbon $startTime, Carbon $endTime, $workstationIds = array())
    {
        parent::__construct($startTime, $endTime);

        // Array given, must be an unique array of valid ids
        if (is_array($workstationIds)) {
            $workstationIds = Arrays::each($workstationIds, function($workstationId)
            {
                if ( ! is_numeric($workstationId)) {
                    throw new LogicException('Workstation id must be a valid id');
                }

                return abs((int) $workstationId);
            });
            $workstationIds = array_unique(array_values($workstationIds));
        }

        $this->workstationIds = $workstationIds;
    }

    /**
     * Make new BookableTimeRange instance using given range and workstationIds
     *
     * @param Agenda\Data\TimeRange $range
     * @param array|null $workstationIds
     * @return Agenda\Data\BookableTimeRange
     */
    public static function rangeByRange(TimeRange $range, $workstationIds = array())
    {
        return new static($range->getStartTime(), $range->getEndTime(), $workstationIds);
    }

    /**
     * Check if workstations are handled
     * when workstations array is null workstations
     * are not handled by Agenda logic
     *
     * @return bool
     */
    public function areWorkstationsHandled()
    {
        return ! is_null($this->workstationIds);
    }

    /**
     * Push new workstation id, check if workstations are handled
     *
     * @param int $workstationId
     * @return void
     * @throws LogicException
     */
    public function pushWorkstationId($workstationId)
    {
        if ( ! $this->areWorkstationsHandled()) {
            throw new LogicException('Workstations are not handled');
        }

        // Unique ids
        if ( ! $this->hasWorkstationId((int) $workstationId)) {
            $this->workstationIds[] = (int) $workstationId;
        }
    }

    /**
     * Get workstation ids, check if workstations are handled
     *
     * @return array
     * @throws LogicException
     */
    public function getWorkstationIds()
    {
        if ( ! $this->areWorkstationsHandled()) {
            throw new LogicException('Workstations are not handled');
        }

        // Workstation ids are immutable out of the class
        return array_values($this->workstationIds);
    }

    /**
     * Check if has a given workstation id, check if workstations are handled
     *
     * @param int $workstationId
     * @return bool
     * @throws LogicException
     */
    public function hasWorkstationId($workstationId)
    {
        if ( ! $this->areWorkstationsHandled()) {
            throw new LogicException('Workstations are not handled');
        }

        return in_array($workstationId, $this->workstationIds);
    }

    /**
     * Remove given workstation id if exist, check if workstations are handled
     *
     * @param int $workstationId
     * @return void
     * @throws LogicException
     */
    public function removeWorkstationId($workstationId)
    {
        if ( ! $this->areWorkstationsHandled()) {
            throw new LogicException('Workstations are not handled');
        }

        $this->workstationIds = Arrays::remove($this->workstationIds, $workstationId);
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

        $bookableTimeRangeStr =  "<br />
            Start ~> $startTimeStr <br />
            End ~> $endTimeStr <br />
        ";

        if ($this->areWorkstationsHandled()) {
            $workstationIdsStr = implode($this->workstationIds, ', ');
            $bookableTimeRangeStr .= "Workstations ~> [$workstationIdsStr] <br />";
        }

        return $bookableTimeRangeStr;
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

        $bookableTimeRangeJson = array(
            'start'        => $startTimeStr,
            'end'          => $endTimeStr
        );

        if ($this->areWorkstationsHandled()) {
            $bookableTimeRangeJson['workstations'] = $this->workstationIds;
        }

        return $bookableTimeRangeJson;
    }
}

