<?php namespace Agenda;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Agenda\Util\Arrays;
use InvalidArgumentException;
use LogicException;

class AgendaCalculator {

    /**
     * The time range using in the calculate process
     *
     * @var Fintyre\Data\TimeRange
     */
    protected $calculateRange;

    /**
     * The time interval of current event using in the calculate process
     *
     * @var Carbon\CarbonInterval
     */
    protected $eventInterval;

    /**
     * List of Fintyre\Data\Event alredy alredy present
     * in current time range
     *
     * @var array
     */
    protected $events;

    /**
     * The list of workstation id using associed to event
     * using the calculate process, if empty the logic of
     * calculate eliminate the workstation control
     *
     * @var array
     */
    protected $workstationIds;

    /**
     * The week woorking ranges, mapped list of Fintyre\Data\TimeRange
     * using array like:
     *
     * [
     *   Carbon::MONDAY => [
     *    new TimeRange(..),
     *    new TimeRange(..)
     *   ],
     *   Carbon::TUESDAY => [
     *     // ..
     *   ],
     *  // ..
     * ]
     *
     * @var array
     */
    protected $weekWorkingRanges;

    /**
     * The special working ranges of particular days,
     * these take precedence over the week working ranges.
     * Use a mapped list of Fintyre\Data\TimeRange, the key is the day in
     * starnder format Y-m-d. Example:
     * [
     *   '2015-09-26' => [
     *      new TimeRange(),
     *      new TimeRange()
     *   ],
     *   // ..
     * ]
     *
     * @var array
     */
    protected $specialWorkingRanges;

    /**
     * List of Carbon\Carbon that indentify festive days
     *
     * @var array
     */
    protected $festiveDays;

    /**
     * Padding interval the interval between the end of range and
     * the start of next calculate interval and between an existing event
     * and the calculate next range
     *
     * @var Carbon\CarbonInterval
     */
    protected $paddingInterval;

    /**
     * Make new AgendaCalculator instance
     *
     * @param Fintyre\Agenda\Data\TimeRange $calculateRange
     * @param Carbon\CarbonInterval $eventInterval
     * @param array $events
     * @param array $workstationIds
     * @param array $weekWorkingRanges
     * @param array $specialWorkingRanges
     * @param array $festiveDays
     * @param Carbon\CarbonInterval|null $paddingInterval
     * @return void
     * @throws InvalidArgumentException|LogicException
     */
    public function __construct(
        Data\TimeRange $calculateRange,
        CarbonInterval $eventInterval,
        array $events,
        array $workstationIds,
        array $weekWorkingRanges,
        array $specialWorkingRanges,
        array $festiveDays,
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

        // Validate attributes
        $this->validateEventInterval();
        $this->validateEvents();
        $this->validateWeekWorkingRanges();
        $this->validateSpecialWorkingRanges();
        $this->validateFestiveDays();
    }

    /**
     * Validate event interval expected to be less then 24 hours
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateEventInterval()
    {
        // Trick for calculate seconds from interval
        $intervalInSeconds = Carbon::now()
            ->diffInSeconds(Carbon::now()->add($this->eventInterval));

        // Must be less then 24 H
        if ( ! ($intervalInSeconds < (24 * 60 * 60))) {
            throw new LogicException('Event interval must be less then 24 Hours');
        }
    }

    /**
     * Validate events expected to be instace of Agenda\Data\Event
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateEvents()
    {
        foreach ($this->events as $event) {
            if ( ! $event instanceof Data\Event) {
                throw new InvalidArgumentException('Events must be
                    instance of Agenda\Data\Event');
            }
        }
    }

    /**
     * Validate week working ranges and ensure that are sort in correct way
     *
     * @return void
     * @throws InvalidArgumentException|LogicException
     */
    protected function validateWeekWorkingRanges()
    {
        foreach ($this->weekWorkingRanges as $dayOfWeek => $dayWorkingRanges) {

            // Validate value to be an array
            if ( ! is_array($dayWorkingRanges)) {
                throw new InvalidArgumentException('Week working ranges values
                    must be array');
            }

            // Validate key to be valid day of week
            if ( ! is_int($dayOfWeek) || ! ($dayOfWeek >= 0 && $dayOfWeek <= 6)) {
                throw new LogicException('Week working ranges key must ba a
                    valid day of week');
            }

            // Validate ranges
            $this->validateRanges($dayWorkingRanges);

            // Order working ranges
            $this->weekWorkingRanges[$dayOfWeek] = $this
                ->orderWorkingRanges($dayWorkingRanges);
        }
    }

    /**
     * Validate special working ranges and ensure that are sort in correct way
     *
     * @return void
     * @throws InvalidArgumentException|LogicException
     */
    protected function validateSpecialWorkingRanges()
    {
        foreach ($this->specialWorkingRanges as $day => $dayWorkingRanges) {

            // Validate value to be an array
            if ( ! is_array($dayWorkingRanges)) {
                throw new InvalidArgumentException('Special working ranges values
                    must be array');
            }

            // Validate key format
            try {
                Carbon::createFromFormat('Y-m-d', $day);
            } catch (InvalidArgumentException $ex) {
                throw new InvalidArgumentException('Special working ranges
                    day key must be in the Y-m-d format');
            }

            // Validate ranges
            $this->validateRanges($dayWorkingRanges);

            // Order working ranges
            $this->specialWorkingRanges[$day] = $this
                ->orderWorkingRanges($dayWorkingRanges);
        }
    }

    /**
     * Validate ranges to be instance of Data\TimeRange, to
     * be in the same day and to be not overlapped
     *
     * @param array $ranges
     * @return void
     * @throws InvalidArgumentException|LogicException
     */
    protected function validateRanges(array $ranges)
    {
        if (count($ranges)) {
            $day = Arrays::first($ranges)->getStartTime();
        }

        foreach ($ranges as $range) {

            if ( ! $range instanceof Data\TimeRange) {
                throw new InvalidArgumentException('Ranges must be
                    instance of Date\TimeRange');
            }

            if ( ! $range->isSameDay()) {
                throw new LogicException('Ranges must be
                    in the same day');
            }

            if ( ! $range->getStartTime()->isSameDay($day)) {
                throw new LogicException('Ranges must be
                    in the same day');
            }

            $overlap = ! is_null(Arrays::find($ranges, function ($rangeToTest)
                use ($range)
            {
                return $range !== $rangeToTest && $range->overlap($rangeToTest);
            }));

            if ($overlap) {
                throw new LogicException('Ranges cannot be overlapped');
            }
        }
    }

    /**
     * Order working ranges, return array ordered
     *
     * @param array $ranges
     * @return array
     */
    protected function orderWorkingRanges(array $ranges)
    {
        return Arrays::sort($ranges, function ($range)
        {
            return $range->getStartTime();
        });
    }

    /**
     * Validate festive days expected to be instace of Carbon\Carbon
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFestiveDays()
    {
        foreach ($this->festiveDays as $festiveDay) {
            if ( ! $festiveDay instanceof Carbon) {
                throw new InvalidArgumentException('Festive days must be
                    instance of Carbon\Carbon');
            }
        }
    }

    /**
     * Calculate available time ranges using based on current
     * instance configuration
     *
     * @return array
     */
    public function calculateRanges()
    {
        if (count($this->workstationIds) === 0) {

            $ranges = $this->calculateRangesOnWorkstation();

            // Map to BookableTimeRange without workstation
            $ranges = Arrays::each($ranges, function($range)
            {
                return Data\BookableTimeRange::rangeByRange($range, null);
            });

        } else {

            // Merge workstation ranges into an unique array
            $ranges = array();

            foreach ($this->workstationIds as $workstationId) {

                // Get ranges of current workstation
                $workstationRanges = $this
                    ->calculateRangesOnWorkstation($workstationId);

                // Merge ranges into new workstation ranges
                $ranges = $this
                    ->mergeIntoBookableRanges($ranges, $workstationRanges, $workstationId);
            }

            // Order ranges by start time
            $ranges = Arrays::sort($ranges, function($range)
            {
                return $range->getStartTime();
            });
        }

        return $ranges;
    }

    /**
     * Calculate available ranges on workstation
     *
     * @param int|null $workstationId
     * @return array
     */
    protected function calculateRangesOnWorkstation($workstationId = null)
    {
        // The container of available ranges on given workstation
        $availableRanges = array();

        // Range using while looping
        $loopRange = Data\TimeRangeInterval::range(
            $this->calculateRange->getStartTime(),
            $this->eventInterval,
            $this->paddingInterval
        );

        // Loop while range is contained in calculate range
        while ($this->calculateRange->contains($loopRange)) {

            // This implementation does not handle range with
            // different days, this range is certainly invalid
            // try the near
            if ( ! $loopRange->isSameDay()) {
                $loopRange = $loopRange->timeRangeInterval(
                    $loopRange->getEndTime()->setTime(0, 0, 0)
                );
                continue;
            }

            // Check day validity
            if ($this->isAClosingDay($loopRange->getStartTime())) {
                // Try first range of tomorrow
                $loopRange = $loopRange->timeRangeInterval(
                    $loopRange->getStartTime()->addDay()->setTime(0, 0, 0)
                );
                continue;
            }

            // Check range validity, is in a valid time range?

            // Get ranges of current day
            $dayRanges = $this->getWorkRangesByDay($loopRange->getStartTime());

            // Is in a day range?
            if ( ! $this->isRangeContainedInRanges($loopRange, $dayRanges)) {
                // Get the near range of current day
                $nearRange = $this->getNearRange($loopRange, $dayRanges);

                if (is_null($nearRange)) {
                    // Try with next day
                    $loopRange = $loopRange->timeRangeInterval(
                        $loopRange->getStartTime()->addDay()->setTime(0, 0, 0)
                    );
                    continue;
                } else {
                    // Try with start of near range
                    $loopRange = $loopRange->timeRangeInterval(
                        $nearRange->getStartTime()
                    );
                    continue;
                }
            }

            // Check overlapped events
            $overlappedEvents = $this
                ->getOverlappedEventsOnWorkstation($loopRange, $workstationId);
            if (count($overlappedEvents)) {

                // Get the overlapped event with the end more far
                $lastEndingEvent = Arrays::last(Arrays::sort($overlappedEvents, function ($range)
                {
                    return $range->getEndTime();
                }));

                // Calculate the new loop range and back to start of loop
                $nextStartTime = $lastEndingEvent->getEndTime();

                // If given add padding time
                if ( ! is_null($this->paddingInterval)) {
                    $nextStartTime->add($this->paddingInterval);
                }

                $loopRange = $loopRange->timeRangeInterval(
                    $nextStartTime
                );
                continue;
            }

            // Ok, this is a valid range
            // save them
            $availableRanges[] = $loopRange->timeRange();

            // Next range
            $loopRange = $loopRange->next();
        }

        return $availableRanges;
    }

    /**
     * Get overlapped events on workstation
     *
     * @param Agenda\Data\TimeRange $range
     * @param int|null $workstationId
     * @return array
     */
    protected function getOverlappedEventsOnWorkstation(Data\TimeRange $range, $workstationId = null)
    {
        return Arrays::filter($this->events, function ($event)
            use ($workstationId, $range)
        {
            return
                $event->getWorkastationId() === $workstationId &&
                $event->overlap($range);
        });
    }

    /**
     * Check if given day is a closing day
     *
     * @param Carbon\Carbon $day
     * @return boolean
     */
    protected function isAClosingDay(Carbon $day)
    {
        // Festive day so is a closing day
        if ($this->isAFestiveDay($day)) {
            return true;
        }

        // Exist a special working range in this day
        // so is not a closing day
        if (isset($this->specialWorkingRanges[$day->toDateString()])) {
            return false;
        }

        // If day of week is not mapped to week working days
        // is a closing day otherwise isn't
        return ! isset($this->weekWorkingRanges[$day->dayOfWeek]);
    }

    /**
     * Check if given day is a festive day
     *
     * @param Carbon\Carbon $day
     * @return boolean
     */
    protected function isAFestiveDay(Carbon $day)
    {
        foreach ($this->festiveDays as $festiveDay) {
            if ($festiveDay->isSameDay($day)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get working ranges based on givend day
     *
     * @param Carbon\Carbon $day
     * @return array
     * @throws LogicException
     */
    protected function getWorkRangesByDay(Carbon $day)
    {
        if (isset($this->specialWorkingRanges[$day->toDateString()])) {
            return $this->getWorkRangesWithFixedDay(
                $day,
                $this->specialWorkingRanges[$day->toDateString()]
            );
        } else if (isset($this->weekWorkingRanges[$day->dayOfWeek])) {
            return $this->getWorkRangesWithFixedDay(
                $day,
                $this->weekWorkingRanges[$day->dayOfWeek]
            );
        } else {
            throw new LogicException('Day ' . $day->toDateString() . ' hasn\'t
                valid working ranges');
        }
    }

    /**
     * Set given day to given ranges, return new array with fixed day
     *
     * @param Carbon\Carbon $day
     * @param array $ranges
     * @return array
     */
    protected function getWorkRangesWithFixedDay(Carbon $day, array $ranges)
    {
        return Arrays::each($ranges, function ($range) use ($day)
        {
            return $range->rangeWithDate($day);
        });
    }

    /**
     * Check if given range is contained in at least one of given cont ranges
     *
     * @param Agenda\Data\TimeRange $range
     * @param array $contRanges
     * @return bool
     */
    protected function isRangeContainedInRanges(Data\TimeRange $range, array $contRanges)
    {
        foreach ($contRanges as $contRange) {
            if ($contRange->contains($range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if given range is in list of given ranges
     *
     * @param Agenda\Data\TimeRange $range
     * @param array $contRanges
     * @return bool
     */
    protected function isRangeInRanges(Data\TimeRange $range, array $contRanges)
    {
        foreach ($contRanges as $contRange) {
            if ($contRange->equal($range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search ranges in range, return key or false if not found
     *
     * @param Agenda\Data\TimeRange $range
     * @param array $contRanges
     * @return string|int|false
     */
    protected function searchRangeInRanges(Data\TimeRange $range, array $contRanges)
    {
        foreach ($contRanges as $key => $contRange) {
            if ($contRange->equal($range)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get near range of given ranges, null if doesn't exist a valid range
     * in cont ranges
     *
     * @param Agenda\Data\TimeRange $range
     * @param array $contRanges
     * @return Agenda\Data\TimeRange|null
     */
    protected function getNearRange(Data\TimeRange $range, array $contRanges)
    {
        foreach ($contRanges as $contRange) {
            if ($contRange->getEndTime()->gte($range->getEndTime())) {
                return $contRange;
            }
        }
    }

    /**
     * Doing the difference between two given range
     * return the result array
     *
     * @param array $ranges
     * @param array $rangesToSub
     * @return array
     */
    protected function rangesDiff(array $ranges, array $rangesToSub)
    {
        // Filter main ranges
        return Arrays::filter($ranges, function ($range)
            use ($rangesToSub)
        {
            // Search for current range in ranges to sub
            // list, if finded this element can be removed
            return ! $this->isRangeInRanges($range, $rangesToSub);
        });
    }

    /**
     * Take a list of bookable ranges and merge given list of ranges
     * if bookable range is in rangens to merge push the current workstation id
     * remaining ranges to merge are mapped to bookable and simple merged
     *
     *
     * @param array $bookableRanges
     * @param array $rangesToMerge
     * @param int $workstationId
     * @return array
     */
    protected function mergeIntoBookableRanges(array $bookableRanges, array $rangesToMerge, $workstationId)
    {
        foreach ($bookableRanges as $bookableRange) {

            // Search for current range in ranges to merge
            $index = $this->searchRangeInRanges($bookableRange, $rangesToMerge);

            if ($index !== false) {
                // Range found

                // Remove range from ranges to merges
                unset($rangesToMerge[$index]);

                // Push workstation id
                $bookableRange->pushWorkstationId($workstationId);
            }
        }

        // Map remaining ranges to merge into bookable ranges
        $rangesToMergeBookable = Arrays::each($rangesToMerge, function($rangeToMerge)
            use ($workstationId)
        {
            return Data\BookableTimeRange::rangeByRange($rangeToMerge, array($workstationId));
        });

        // Return new array of bookable ranges merged
        return Arrays::merge($bookableRanges, $rangesToMergeBookable);
    }
}

