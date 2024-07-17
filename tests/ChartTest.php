<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Chart;
use PHPUnit\Framework\TestCase;

class ChartTest extends TestCase
{
    protected function getChart($language = "en-US"): Chart
    {
        $config = new Config();
        $config->language = $language;

        return new Chart($config);
    }

    public function testBoxOffice()
    {
        $chart = $this->getChart();
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
        $chart = $this->getChart();
        $result = $chart->getTop250Movies();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertEquals(1, $result[0]['rank']);
        $this->assertEquals("tt0111161", $result[0]['id']);
        $this->assertEquals("The Shawshank Redemption", $result[0]['title']);
        $this->assertEquals("movie", $result[0]['type']);
        $this->assertEquals("https://m.media-amazon.com/images/M/MV5BNDE3ODcxYzMtY2YzZC00NmNlLWJiNDMtZDViZWM2MzIxZDYwXkEyXkFqcGdeQXVyNjAwNDUxODI@._V1_.jpg", $result[0]['image']);
        $this->assertEquals(1994, $result[0]['year']);
        $this->assertGreaterThan(9, $result[0]['rating']);
        $this->assertGreaterThan(2916000, $result[0]['votes']);
    }

}
