<?php

use Hooshid\ImdbScraper\Chart;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    public function testBoxOffice()
    {
        $chart = new Chart();
        $boxOffice = $chart->getBoxOffice();

        $this->assertIsArray($boxOffice);
        $this->assertNotEmpty($boxOffice);

        foreach ($boxOffice as $film) {
            $this->assertIsArray($film);
            $this->assertCount(5, $film);
            $this->assertTrue(strlen($film['id']) >= 9 and strlen($film['id']) <= 10);
            $this->assertTrue(is_numeric($film['weekend']));
            $this->assertTrue(is_numeric($film['gross']));
        }
    }

    public function testTop250Movies()
    {
        $chart = new Chart();
        $result = $chart->getList("TOP_250");

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertEquals(1, $result[0]['rank']);
        $this->assertEquals("tt0111161", $result[0]['id']);
        $this->assertEquals("The Shawshank Redemption", $result[0]['title']);
        $this->assertEquals("Movie", $result[0]['type']);
        $this->assertEquals("https://m.media-amazon.com/images/M/MV5BMDAyY2FhYjctNDc5OS00MDNlLThiMGUtY2UxYWVkNGY2ZjljXkEyXkFqcGc@._V1_.jpg", $result[0]['imageUrl']['original']);
        $this->assertEquals(1994, $result[0]['year']);
        $this->assertGreaterThan(9, $result[0]['rating']);
        $this->assertGreaterThan(2916000, $result[0]['votes']);
    }

    public function testTop250TV()
    {
        $chart = new Chart();
        $result = $chart->getList("TOP_250_TV");

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertEquals(1, $result[0]['rank']);
        $this->assertEquals("tt0903747", $result[0]['id']);
        $this->assertEquals("Breaking Bad", $result[0]['title']);
        $this->assertEquals("TV Series", $result[0]['type']);
        $this->assertEquals("https://m.media-amazon.com/images/M/MV5BMzU5ZGYzNmQtMTdhYy00OGRiLTg0NmQtYjVjNzliZTg1ZGE4XkEyXkFqcGc@._V1_.jpg", $result[0]['imageUrl']['original']);
        $this->assertEquals(2008, $result[0]['year']);
        $this->assertGreaterThan(9, $result[0]['rating']);
        $this->assertGreaterThan(2150000, $result[0]['votes']);
    }

}
