<?php

use Hooshid\ImdbScraper\KeywordSearch;
use PHPUnit\Framework\TestCase;

class KeywordTest extends TestCase
{
    public function testKeywordSearch()
    {
        $keywordSearch = new KeywordSearch();
        $results = $keywordSearch->search('Gold');

        $this->assertIsArray($results);
        $this->assertCount(50, $results);

        $this->assertEquals('kw0011635', $results[0]['id']);
        $this->assertEquals('gold', $results[0]['keyword']);
        $this->assertGreaterThan(1900, $results[0]['total_titles']);
    }
}
