<?php namespace Agenda;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use BadMethodCallException;

class Agenda {

    /**
     * The calculateRange attribute of AgendaCalculator
     *
     * @var Fintyre\Data\TimeRange
     */
    protected $calculateRange;

    /**
     * The eventInterval attribute of AgendaCalculator
     *
     * @var Carbon\CarbonInterval
     */
    protected $eventInterval;

    /**
     * The events attribute of AgendaCalculator
     *
     * @var array
     */
    protected $events;

    /**
     * The workstationIds attribute of AgendaCalculator
     *
     * @var array
     */
    protected $workstationIds;

    /**
     * The weekWorkingRanges attribute of AgendaCalculator
     *
     * @var array
     */
    protected $weekWorkingRanges;

    /**
     * The specialWorkingRanges attribute of AgendaCalculator
     *
     * @var array
     */
    protected $specialWorkingRanges;

    /**
     * The festiveDays attribute of AgendaCalculator
     *
     * @var array
     */
    protected $festiveDays;

    /**
     * The paddingInterval attribute of AgendaCalculator
     *
     * @var Carbon\CarbonInterval
     */
    protected $paddingInterval;

    /**
     * Make new Agenda instance
     *
     * @param Fintyre\Agenda\Data\TimeRange|null $calculateRange
     * @param Carbon\CarbonInterval|null $eventInterval
     * @param array $events
     * @param array $workstationIds
     * @param array $weekWorkingRanges
     * @param array $specialWorkingRanges
     * @param array $festiveDays
     * @param Carbon\CarbonInterval|null $paddingInterval
     * @return void
     */
    public function __construct(
        Data\TimeRange $calculateRange = null,
        CarbonInterval $eventInterval = null,
        array $events = array(),
        array $workstationIds = array(),
        array $weekWorkingRanges = array(),
        array $specialWorkingRanges = array(),
        array $festiveDays = array(),
        CarbonInterval $paddingInterval = null
    ) {
        $this->calculateRange = $calculateRange;
        $this->eventInterval = $eventInterval;
        $this->events = $events;
        $this->workstationIds = $workstationIds;
        $this->weekWorkingRanges = $weekWorkingRanges;
        $this->specialWorkingRanges = $specialWorkingRanges;
        $this->festiveDays = $festiveDays;
        $this->paddingInterval = $paddingInterval;
    }

    /**
     * Make Agenda instance with defaults attributes
     *
     * @return Agenda\Agenda
     */
    public static function agenda()
    {
        return new static();
    }

    /**
     * Set calculate range
     *
     * @param Agenda\Data\TimeRange $calculateRange
     * @return Agenda\Agenda
     */
    public function setCalculateRange(Data\TimeRange $calculateRange)
    {
        $this->calculateRange = $calculateRange;
        return $this;
    }

    /**
     * Set event interval
     *
     * @param Carbon\CarbonInterval $eventInterval
     * @return Agenda\Agenda
     */
    public function setEventInterval(CarbonInterval $eventInterval)
    {
        $this->eventInterval = $eventInterval;
        return $this;
    }

    /**
     * Set events
     *
     * @param array $events
     * @return Agenda\Agenda
     */
    public function setEvents(array $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Set workstation ids
     *
     * @param array $workstationIds
     * @return Agenda\Agenda
     */
    public function setWorkstationIds(array $workstationIds)
    {
        $this->workstationIds = $workstationIds;
        return $this;
    }

    /**
     * Set week working ranges
     *
     * @param array $weekWorkingRanges
     * @return Agenda\Agenda
     */
    public function setWeekWorkingRanges(array $weekWorkingRanges)
    {
        $this->weekWorkingRanges = $weekWorkingRanges;
        return $this;
    }

    /**
     * Set special working ranges
     *
     * @param array $specialWorkingRanges
     * @return Agenda\Agenda
     */
    public function setSpecialWorkingRanges(array $specialWorkingRanges)
    {
        $this->specialWorkingRanges = $specialWorkingRanges;
        return $this;
    }

    /**
     * Set festive days
     *
     * @param array $festiveDays
     * @return Agenda\Agenda
     */
    public function setFestiveDays(array $festiveDays)
    {
        $this->festiveDays = $festiveDays;
        return $this;
    }

    /**
     * Set padding interval
     *
     * @param Carbon\CarbonInterval|null $paddingInterval
     * @return Agenda\Agenda
     */
    public function setPaddingInterval(CarbonInterval $paddingInterval = null)
    {
        $this->paddingInterval = $paddingInterval;
        return $this;
    }

    /**
     * Get calculate range
     *
     * @return Agenda\Data\TimeRange
     */
    public function getCalculateRange()
    {
        return $this->calculateRange;
    }

    /**
     * Get event interval
     *
     * @return Carbon\CarbonInterval
     */
    public function getEventInterval()
    {
        return $this->eventInterval;
    }

    /**
     * Get events
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get workstation ids
     *
     * @return array
     */
    public function getWorkstationIds()
    {
        return $this->workstationIds;
    }

    /**
     * Get week working ranges
     *
     * @return array
     */
    public function getWeekWorkingRanges()
    {
        return $this->weekWorkingRanges;
    }

    /**
     * Get special working ranges
     *
     * @return array
     */
    public function getSpecialWorkingRanges()
    {
        return $this->specialWorkingRanges;
    }

    /**
     * Get festive days
     *
     * @return array
     */
    public function getFestiveDays()
    {
        return $this->festiveDays;
    }

    /**
     * Get padding interval
     *
     * @return Carbon\CarbonInterval|null
     */
    public function getPaddingInterval()
    {
        return $this->paddingInterval;
    }

    /**
     * Instace AgendaCalculator
     *
     * @return Agenda\AgendaCalculator
     * @throws InvalidArgumentException|LogicException
     */
    public function instance()
    {
        return new AgendaCalculator(
            $this->calculateRange,
            $this->eventInterval,
            $this->events,
            $this->workstationIds,
            $this->weekWorkingRanges,
            $this->specialWorkingRanges,
            $this->festiveDays,
            $this->paddingInterval
        );
    }

    /**
     * Call method on AgendaCalculator instance
     *
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($name, $args)
    {
        $agenda = $this->instance();

        if (! method_exists($agenda, $name)) {
            throw new BadMethodCallException("Invalid method [{$name}] for Agenda");
        }

        return call_user_func_array(array($agenda, $name), $args);
    }
}

