<?php

use Hooshid\ImdbScraper\TitleSearch;
use PHPUnit\Framework\TestCase;

class TitleSearchTest extends TestCase
{
    public function testSearch()
    {
        $name = new TitleSearch();
        $result = $name->search(['searchTerm' => 'I Saw the Devil', 'types' => 'movie']);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // I Saw the Devil
        $this->assertIsArray($result[0]['imageUrl']);
        $this->assertEquals('tt1588170', $result[0]['id']);
        $this->assertEquals('I Saw the Devil', $result[0]['title']);
        $this->assertEquals('Movie', $result[0]['type']);
        $this->assertEquals('2010', $result[0]['year']);

    }
}
