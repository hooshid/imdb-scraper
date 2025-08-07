<?php

use Hooshid\ImdbScraper\NameSearch;
use PHPUnit\Framework\TestCase;

class NameSearchTest extends TestCase
{
    public function testSearch()
    {
        $name = new NameSearch();
        $data = $name->search(['name' => 'Depp']);
        $results = $data['results'];

        // Total result
        $this->assertGreaterThan(35, $data['total']);

        $this->assertIsArray($results);
        $this->assertGreaterThan(35, count($results));

        foreach ($results as $result) {
            if ($result['id'] == "nm0000136" || $result['name'] == "Johnny Depp") {
                $this->assertIsArray($result['image']);
                $this->assertIsInt($result['index']);
                $this->assertEquals('nm0000136', $result['id']);
                $this->assertEquals('Johnny Depp', $result['name']);
                $this->assertEquals('Actor, Producer, Director', implode(", ", $result['professions']));
                $this->assertGreaterThan(250, strlen($result['bio']));
                $this->assertIsArray($result['known_for']);
                $this->assertCount(5, $result['known_for']);
            } else if ($result['id'] == "nm6675440" || $result['name'] == "Lily-Rose Depp") {
                $this->assertIsArray($result['image']);
                $this->assertLessThan(3, $result['index']);
                $this->assertEquals('nm6675440', $result['id']);
                $this->assertEquals('Lily-Rose Depp', $result['name']);
                $this->assertEquals('Actress, Composer, Soundtrack', implode(", ", $result['professions']));
                $this->assertGreaterThan(250, strlen($result['bio']));
                $this->assertIsArray($result['known_for']);
                $this->assertCount(5, $result['known_for']);
            }
        }
    }

    public function testBornToday()
    {
        $name = new NameSearch();
        $data = $name->search(['birthMonthDay' => date("m-d")]);
        $results = $data['results'];

        $this->assertIsArray($results);
        $this->assertCount(50, $results);

        // 1. First result
        $this->assertEquals(1, $results[0]['index']);
        $this->assertNotNull($results[0]['id']);
        $this->assertNotNull($results[0]['name']);
        $this->assertIsArray($results[0]['image']);
        $this->assertNotNull($results[0]['professions']);
        $this->assertNotNull($results[0]['bio']);
        $this->assertIsArray($results[0]['known_for']);
    }

    public function testDied()
    {
        $name = new NameSearch();
        $data = $name->search(['deathDateRangeStart' => '2025-01-16', 'deathDateRangeEnd' => '2025-01-16']);
        $results = $data['results'];

        $this->assertIsArray($results);
        $this->assertCount(17, $results);

        // 1. David Lynch
        $this->assertIsArray($results[0]['image']);
        $this->assertEquals(1, $results[0]['index']);
        $this->assertEquals('nm0000186', $results[0]['id']);
        $this->assertEquals('David Lynch', $results[0]['name']);
        $this->assertEquals('Writer, Director, Producer', implode(", ", $results[0]['professions']));
        $this->assertGreaterThan(250, strlen($results[0]['bio']));

        $this->assertEquals('tt0098936', $results[0]['known_for'][0]['id']);
        $this->assertEquals('Twin Peaks', $results[0]['known_for'][0]['title']);
        $this->assertEquals('1990', $results[0]['known_for'][0]['year']);
        $this->assertEquals('1991', $results[0]['known_for'][0]['end_year']);
    }
}
