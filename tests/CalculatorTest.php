<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;

class CalculatorTest extends TestFixture
{
    public function testCalculatorBasicWithNothing()
    {
        $ranges = Agenda\Agenda::agenda()
            ->setCalculateRange($this->tr(
                '2015-09-07',
                '2015-09-08'
            ))
            ->setEventInterval(CarbonInterval::minutes(60))
            ->calculateRanges();

        $this->assertEmpty($ranges);
    }

    public function testCalculatorWeekWorkingRanges()
    {
        $ranges = Agenda\Agenda::agenda()
            ->setCalculateRange($this->tr(
                '2015-09-07',
                '2015-09-14 13:30'
            ))
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

        $this->assertBookableTimeRangesEqual($ranges, array(
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
        ));
    }

    public function testCalculatorPaddingInterval()
    {
        $ranges = Agenda\Agenda::agenda()
            ->setCalculateRange($this->tr(
                '2015-09-07',
                '2015-09-14 13:30'
            ))
            ->setEventInterval(CarbonInterval::minutes(60))
            ->setPaddingInterval(CarbonInterval::minutes(5))
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

        $this->assertBookableTimeRangesEqual($ranges, array(
            $this->btr('2015-09-07 09:00:00', '2015-09-07 10:00:00'),
            $this->btr('2015-09-07 10:05:00', '2015-09-07 11:05:00'),
            $this->btr('2015-09-07 14:00:00', '2015-09-07 15:00:00'),
            $this->btr('2015-09-07 15:05:00', '2015-09-07 16:05:00'),
            $this->btr('2015-09-07 16:10:00', '2015-09-07 17:10:00'),

            $this->btr('2015-09-09 10:00:00', '2015-09-09 11:00:00'),
            $this->btr('2015-09-09 11:50:00', '2015-09-09 12:50:00'),
            $this->btr('2015-09-09 12:55:00', '2015-09-09 13:55:00'),
            $this->btr('2015-09-09 14:00:00', '2015-09-09 15:00:00'),
            $this->btr('2015-09-09 15:05:00', '2015-09-09 16:05:00'),
            $this->btr('2015-09-09 16:10:00', '2015-09-09 17:10:00'),

            $this->btr('2015-09-11 14:00:00', '2015-09-11 15:00:00'),
            $this->btr('2015-09-11 15:05:00', '2015-09-11 16:05:00'),
            $this->btr('2015-09-11 16:10:00', '2015-09-11 17:10:00'),
            $this->btr('2015-09-11 17:15:00', '2015-09-11 18:15:00'),

            $this->btr('2015-09-14 09:00:00', '2015-09-14 10:00:00'),
            $this->btr('2015-09-14 10:05:00', '2015-09-14 11:05:00')
        ));
    }

}
