<?php

use Hooshid\ImdbScraper\TitleSearch;
use PHPUnit\Framework\TestCase;

class TitleSearchTest extends TestCase
{
    public function testSearch()
    {
        $titleSearch = new TitleSearch();
        $data = $titleSearch->search(['searchTerm' => 'I Saw the Devil', 'types' => 'movie']);
        $results = $data['results'];

        // Total result
        $this->assertEquals(3, $data['total']);

        $this->assertIsArray($results);
        $this->assertCount(3, $results);

        // I Saw the Devil
        $this->assertEquals('tt1588170', $results[0]['id']);
        $this->assertEquals('https://www.imdb.com/title/tt1588170/', $results[0]['url']);
        $this->assertEquals('I Saw the Devil', $results[0]['title']);
        $this->assertEquals('Movie', $results[0]['type']);
        $this->assertEquals('2010', $results[0]['year']);
        $this->assertEquals('A secret agent exacts revenge on a serial killer through a series of captures and releases.', $results[0]['plot']);
        $this->assertEquals("2:24:00", $results[0]['runtime_formatted']);
        $this->assertEquals(144, $results[0]['runtime_minutes']);
        $this->assertEquals(8640, $results[0]['runtime_seconds']);
        $this->assertGreaterThan(7.5, $results[0]['rating']);
        $this->assertGreaterThan(150000, $results[0]['votes']);
        $this->assertGreaterThan(65, $results[0]['metacritic']);
        $this->assertIsArray($results[0]['image']);
    }
}
