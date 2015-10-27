<?php
ini_set('display_errors', 1);
use Carbon\Carbon;
use Carbon\CarbonInterval;

class CalculatorTest extends PHPUnit_Framework_TestCase
{
    public function testCalculatorBasicWithNothing()
    {
        $ranges = Agenda\Agenda::agenda()
            ->setCalculateRange(
                new Agenda\Data\TimeRange(
                    $this->tr('2015-09-07'),
                    $this->tr('2015-09-08')
                )
            )
            ->setEventInterval(CarbonInterval::minutes(60))
            ->calculateRanges();

        $this->assertEquals(count($ranges), 0);
    }

    public function testCalculatorWeekWorkingRanges()
    {
        $ranges = Agenda\Agenda::agenda()
            ->setCalculateRange($this->tr('2015-09-07', '2015-09-14 13:30'))
            ->setEventInterval(CarbonInterval::minutes(60))
            ->setWeekWorkingRanges(array(
                Carbon::MONDAY => array(
                    $this->tr('09:00', '12:00'),
                    $this->tr('14:00', '18:00'),
                ),
                Carbon::WEDNESDAY => array(
                    $this->tr('10:00', '11:10'),
                    $this->tr('11:30', '11:40'),
                    $this->tr('11:50', '18:00'),
                ),
                Carbon::FRIDAY => array(
                    $this->tr('14:00', '18:30'),
                )
            ))
            ->calculateRanges();

        $expectedRanges = array(
            $this->btr('2015-09-07 09:00:00', '2015-09-07 10:00:00'),
            $this->btr('2015-09-07 10:00:00', '2015-09-07 11:00:00'),
            $this->btr('2015-09-07 11:00:00', '2015-09-07 12:00:00'),
            $this->btr('2015-09-07 14:00:00', '2015-09-07 15:00:00'),
            $this->btr('2015-09-07 15:00:00', '2015-09-07 16:00:00'),
            $this->btr('2015-09-07 16:00:00', '2015-09-07 17:00:00'),
            $this->btr('2015-09-07 17:00:00', '2015-09-07 18:00:00'),

            $this->btr('2015-09-09 10:00:00', '2015-09-09 11:00:00'),
            $this->btr('2015-09-09 11:50:00', '2015-09-09 12:50:00'),
            $this->btr('2015-09-09 12:50:00', '2015-09-09 13:50:00'),
            $this->btr('2015-09-09 13:50:00', '2015-09-09 14:50:00'),
            $this->btr('2015-09-09 14:50:00', '2015-09-09 15:50:00'),
            $this->btr('2015-09-09 15:50:00', '2015-09-09 16:50:00'),
            $this->btr('2015-09-09 16:50:00', '2015-09-09 17:50:00'),

            $this->btr('2015-09-11 14:00:00', '2015-09-11 15:00:00'),
            $this->btr('2015-09-11 15:00:00', '2015-09-11 16:00:00'),
            $this->btr('2015-09-11 16:00:00', '2015-09-11 17:00:00'),
            $this->btr('2015-09-11 17:00:00', '2015-09-11 18:00:00'),

            $this->btr('2015-09-14 09:00:00', '2015-09-14 10:00:00'),
            $this->btr('2015-09-14 10:00:00', '2015-09-14 11:00:00'),
            $this->btr('2015-09-14 11:00:00', '2015-09-14 12:00:00'),
        );

        $this->assertEquals(count($expectedRanges), count($ranges));
        $this->assertContainsOnlyInstancesOf('Agenda\Data\BookableTimeRange', $ranges);
        $this->assertTrue($this->areBookableRangesEquals($ranges, $expectedRanges));
    }

    private function printRanges(array $ranges)
    {
        echo "\n";
        foreach ($ranges as $range) {
            echo json_encode($range->jsonSerialize()) . "\n";
        }
        echo "\n";
    }

    private function tr($start, $end)
    {
        return new Agenda\Data\TimeRange(
            Carbon::parse($start),
            Carbon::parse($end)
        );
    }

    private function btr($start, $end, $workstationIds = null)
    {
        return new Agenda\Data\BookableTimeRange(
            Carbon::parse($start),
            Carbon::parse($end),
            $workstationIds
        );
    }

    private function areBookableRangesEquals(array $ranges1, array $ranges2)
    {
        for ($i = 0; $i < count($ranges1); $i++) {

            if (!$ranges1[$i]->equal($ranges2[$i])) {
                return false;
            }

            if ($ranges1[$i]->areWorkstationsHandled() !== $ranges2[$i]->areWorkstationsHandled()) {
                return false;
            }

            if ($ranges1[$i]->areWorkstationsHandled()) {
                if ($ranges1[$i]->getWorkstationIds() != $ranges2[$i]->getWorkstationIds()) {
                    return false;
                }
            }
        }

        return true;
    }

}
