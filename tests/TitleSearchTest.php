<?php

use Hooshid\ImdbScraper\TitleSearch;
use PHPUnit\Framework\TestCase;

class TitleSearchTest extends TestCase
{
    public function testSearch()
    {
        $titleSearch = new TitleSearch();
        $result = $titleSearch->search(['searchTerm' => 'I Saw the Devil', 'types' => 'movie']);

        // Total result
        $this->assertEquals(3, $titleSearch->total());

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // I Saw the Devil
        $this->assertEquals('tt1588170', $result[0]['id']);
        $this->assertEquals('https://www.imdb.com/title/tt1588170', $result[0]['url']);
        $this->assertEquals('I Saw the Devil', $result[0]['title']);
        $this->assertEquals('Movie', $result[0]['type']);
        $this->assertEquals('2010', $result[0]['year']);
        $this->assertEquals('A secret agent exacts revenge on a serial killer through a series of captures and releases.', $result[0]['plot']);
        $this->assertEquals(144, $result[0]['runtime']);
        $this->assertGreaterThan(7.5, $result[0]['rating']);
        $this->assertGreaterThan(150000, $result[0]['votes']);
        $this->assertGreaterThan(65, $result[0]['metacritic']);
        $this->assertIsArray($result[0]['image']);
    }
}
