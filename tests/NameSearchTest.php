<?php

use Hooshid\ImdbScraper\NameSearch;
use PHPUnit\Framework\TestCase;

class NameSearchTest extends TestCase
{
    public function testSearch()
    {
        $name = new NameSearch();
        $result = $name->search(['name' => 'Depp']);

        $this->assertIsArray($result);
        $this->assertCount(37, $result);

        // 1. Lily-Rose Depp
        $this->assertIsArray($result[0]['imageUrl']);
        $this->assertEquals(1, $result[0]['index']);
        $this->assertEquals('nm6675440', $result[0]['id']);
        $this->assertEquals('Lily-Rose Depp', $result[0]['name']);
        $this->assertEquals('Actress, Composer, Soundtrack', implode(", ", $result[0]['professions']));
        $this->assertGreaterThan(250, strlen($result[0]['bio']));

        // 2. Johnny Depp
        $this->assertIsArray($result[1]['imageUrl']);
        $this->assertEquals(2, $result[1]['index']);
        $this->assertEquals('nm0000136', $result[1]['id']);
        $this->assertEquals('Johnny Depp', $result[1]['name']);
        $this->assertEquals('Actor, Producer, Director', implode(", ", $result[1]['professions']));
        $this->assertGreaterThan(250, strlen($result[1]['bio']));
    }

    public function testBornToday()
    {
        $name = new NameSearch();
        $result = $name->search(['birthMonthDay' => date("m-d")]);

        $this->assertIsArray($result);
        $this->assertCount(50, $result);

        // 1. First result
        $this->assertEquals(1, $result[0]['index']);
        $this->assertNotNull($result[0]['id']);
        $this->assertNotNull($result[0]['name']);
        $this->assertIsArray($result[0]['imageUrl']);
        $this->assertNotNull($result[0]['professions']);
        $this->assertNotNull($result[0]['bio']);
    }

    public function testDied()
    {
        $name = new NameSearch();
        $result = $name->search(['deathDateRangeStart' => '2025-01-15','deathDateRangeEnd' => '2025-01-15']);

        $this->assertIsArray($result);
        $this->assertCount(20, $result);

        // 1. David Lynch
        $this->assertIsArray($result[0]['imageUrl']);
        $this->assertEquals(1, $result[0]['index']);
        $this->assertEquals('nm0000186', $result[0]['id']);
        $this->assertEquals('David Lynch', $result[0]['name']);
        $this->assertEquals('Writer, Director, Producer', implode(", ", $result[0]['professions']));
        $this->assertGreaterThan(250, strlen($result[0]['bio']));
    }
}
