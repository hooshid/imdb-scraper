<?php

use Hooshid\ImdbScraper\Calendar;
use PHPUnit\Framework\TestCase;

class CalendarTest extends TestCase
{
    public function testComingSoon()
    {
        $calendar = new Calendar();
        $result = $calendar->comingSoon();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertStringStartsWith('tt', $result[0]['id']);
        $this->assertIsString($result[0]['title']);
        $this->assertEquals(10, strlen($result[0]['release_date']));
        $this->assertIsArray($result[0]['genres']);
        $this->assertIsArray($result[0]['cast']);
    }
}
