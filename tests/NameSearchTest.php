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
        $this->assertEquals(37, $data['total']);

        $this->assertIsArray($results);
        $this->assertCount(37, $results);

        // Johnny Depp
        $johnny = $results[0];
        $this->assertIsArray($johnny['image']);
        $this->assertEquals(1, $johnny['index']);
        $this->assertEquals('nm0000136', $johnny['id']);
        $this->assertEquals('Johnny Depp', $johnny['name']);
        $this->assertEquals('Actor, Producer, Director', implode(", ", $johnny['professions']));
        $this->assertGreaterThan(250, strlen($johnny['bio']));
        $this->assertIsArray($johnny['known_for']);
        $this->assertCount(5, $johnny['known_for']);

        // Lily-Rose Depp
        $lily = $results[1];
        $this->assertIsArray($lily['image']);
        $this->assertEquals(2, $lily['index']);
        $this->assertEquals('nm6675440', $lily['id']);
        $this->assertEquals('Lily-Rose Depp', $lily['name']);
        $this->assertEquals('Actress, Composer, Soundtrack', implode(", ", $lily['professions']));
        $this->assertGreaterThan(250, strlen($lily['bio']));
        $this->assertIsArray($lily['known_for']);
        $this->assertCount(5, $lily['known_for']);
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
        $data = $name->search(['deathDateRangeStart' => '2025-01-16','deathDateRangeEnd' => '2025-01-16']);
        $results = $data['results'];

        $this->assertIsArray($results);
        $this->assertCount(16, $results);

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
