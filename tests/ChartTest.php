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

        $this->assertIsString($boxOffice['weekend_start_date']);
        $this->assertIsString($boxOffice['weekend_end_date']);

        foreach ($boxOffice['list'] as $row) {
            $this->assertIsArray($row);
            $this->assertCount(10, $row);
            $this->assertStringStartsWith("tt", $row['id']);
            $this->assertNotNull($row['rating']);
            $this->assertNotNull($row['votes']);
            $this->assertIsInt(is_numeric($row['lifetime_gross_amount']));
            $this->assertEquals("USD", $row['lifetime_gross_currency']);
            $this->assertIsInt($row['weekend_gross_amount']);
            $this->assertEquals("USD", $row['weekend_gross_currency']);
            $this->assertIsInt($row['weeks_released']);
            $this->assertIsArray($row['image']);
        }
    }

    public function testTop250Movies()
    {
        $chart = new Chart();
        $result = $chart->getList("TOP_250");

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertEquals("tt0111161", $result[0]['id']);
        $this->assertEquals("The Shawshank Redemption", $result[0]['title']);
        $this->assertEquals(1, $result[0]['rank']);
        $this->assertEquals("Movie", $result[0]['type']);
        $this->assertEquals(142, $result[0]['runtime']);
        $this->assertEquals(1994, $result[0]['year']);
        $this->assertGreaterThan(9, $result[0]['rating']);
        $this->assertGreaterThan(3000000, $result[0]['votes']);
        $this->assertIsArray($result[0]['image']);
        $this->assertEquals("https://m.media-amazon.com/images/M/MV5BMDAyY2FhYjctNDc5OS00MDNlLThiMGUtY2UxYWVkNGY2ZjljXkEyXkFqcGc@._V1_.jpg", $result[0]['image']['url']);
    }

    public function testTop250TV()
    {
        $chart = new Chart();
        $result = $chart->getList("TOP_250_TV");

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertEquals("tt0903747", $result[0]['id']);
        $this->assertEquals("Breaking Bad", $result[0]['title']);
        $this->assertEquals(1, $result[0]['rank']);
        $this->assertEquals("TV Series", $result[0]['type']);
        $this->assertEquals(45, $result[0]['runtime']);
        $this->assertEquals(2008, $result[0]['year']);
        $this->assertGreaterThan(9, $result[0]['rating']);
        $this->assertGreaterThan(2300000, $result[0]['votes']);
        $this->assertIsArray($result[0]['image']);
        $this->assertEquals("https://m.media-amazon.com/images/M/MV5BMzU5ZGYzNmQtMTdhYy00OGRiLTg0NmQtYjVjNzliZTg1ZGE4XkEyXkFqcGc@._V1_.jpg", $result[0]['image']['url']);
    }

    public function testMostPopularNames()
    {
        $chart = new Chart();
        $result = $chart->getMostPopularNames();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertStringStartsWith('nm', $result[0]['id']);
        $this->assertIsString($result[0]['name']);
        $this->assertEquals(1, $result[0]['rank']);
        $this->assertIsArray($result[0]['professions']);
        $this->assertIsArray($result[0]['known_for']);
        $this->assertIsArray($result[0]['image']);
    }

}
