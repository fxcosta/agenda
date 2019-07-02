<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Framework\TestCase;

class TestFixture extends TestCase
{

    protected function tr($start, $end)
    {
        return new Agenda\Data\TimeRange(
            Carbon::parse($start),
            Carbon::parse($end)
        );
    }

    protected function btr($start, $end, $workstationIds = null)
    {
        return new Agenda\Data\BookableTimeRange(
            Carbon::parse($start),
            Carbon::parse($end),
            $workstationIds
        );
    }

    protected function event($start, $end, $workstationId = null)
    {
        return new Agenda\Data\Event(
            Carbon::parse($start),
            Carbon::parse($end),
            $workstationId
        );
    }

    protected function assertValidCollectionOfBookableTimeRanges($ranges)
    {
        $this->assertContainsOnlyInstancesOf('Agenda\Data\BookableTimeRange', $ranges);
    }

    protected function assertBookableTimeRangesEqual($ranges1, $ranges2)
    {
        $this->assertValidCollectionOfBookableTimeRanges($ranges1);
        $this->assertValidCollectionOfBookableTimeRanges($ranges2);

        $this->assertEquals(count($ranges1), count($ranges2));

        for ($i = 0; $i < count($ranges1); $i++) {

            $range1 = $ranges1[$i];
            $range2 = $ranges2[$i];

            $this->assertTrue($range1->equal($range2));
            $this->assertEquals($range1->areWorkstationsHandled(), $range2->areWorkstationsHandled());


            if ($range1->areWorkstationsHandled()) {
                $this->assertEquals($range1->getWorkstationIds(), $range2->getWorkstationIds());
            }
        }
    }

    protected function printRanges(array $ranges)
    {
        echo "\n";
        foreach ($ranges as $range) {
            echo json_encode($range->jsonSerialize()) . "\n";
        }
        echo "\n";
    }
}
