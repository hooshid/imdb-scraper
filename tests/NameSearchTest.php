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

        // Johnny Depp
        $johnny = $result[0];
        $this->assertIsArray($johnny['imageUrl']);
        $this->assertEquals(1, $johnny['index']);
        $this->assertEquals('nm0000136', $johnny['id']);
        $this->assertEquals('Johnny Depp', $johnny['name']);
        $this->assertEquals('Actor, Producer, Director', implode(", ", $johnny['professions']));
        $this->assertGreaterThan(250, strlen($johnny['bio']));

        // Lily-Rose Depp
        $lily = $result[1];
        $this->assertIsArray($lily['imageUrl']);
        $this->assertEquals(2, $lily['index']);
        $this->assertEquals('nm6675440', $lily['id']);
        $this->assertEquals('Lily-Rose Depp', $lily['name']);
        $this->assertEquals('Actress, Composer, Soundtrack', implode(", ", $lily['professions']));
        $this->assertGreaterThan(250, strlen($lily['bio']));

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
        $this->assertCount(23, $result);

        // 1. David Lynch
        $this->assertIsArray($result[0]['imageUrl']);
        $this->assertEquals(1, $result[0]['index']);
        $this->assertEquals('nm0000186', $result[0]['id']);
        $this->assertEquals('David Lynch', $result[0]['name']);
        $this->assertEquals('Writer, Director, Producer', implode(", ", $result[0]['professions']));
        $this->assertGreaterThan(250, strlen($result[0]['bio']));
    }
}
