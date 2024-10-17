<?php

use Hooshid\ImdbScraper\Calendar;
use PHPUnit\Framework\TestCase;

class CalendarTest extends TestCase
{
    public function testComingSoon()
    {
        $calendar = new Calendar();
        $comingSoon = $calendar->comingSoon();

        $this->assertIsArray($comingSoon);
        $this->assertNotEmpty($comingSoon);
    }
}
