<?php namespace Agenda\Data;

use Carbon\Carbon;

class Event extends TimeRange {

    /**
     * Identify the workstation of current event
     * if null mean that there are no workstation
     *
     * @var int|null
     */
    protected $workstationId;

    /**
     * Make new Event instance
     *
     * @param Carbon\Carbon $startTime
     * @param Carbon\Carbon $endTime
     * @param int|null $workstationId
     * @return void
     * @throws LogicException
     */
    public function __construct(Carbon $startTime, Carbon $endTime, $workstationId = null)
    {
        parent::__construct($startTime, $endTime);

        $this->workstationId = $workstationId;
    }

    /**
     * Get workstation id
     *
     * @return int|null
     */
    public function getWorkastationId()
    {
        return $this->workstationId;
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
            'start'       => $startTimeStr,
            'end'         => $endTimeStr,
            'workstation' => $this->workstationId
        );
    }
}

