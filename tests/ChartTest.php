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
        $charts = $this->getChart();
        $boxOffice = $charts->getBoxOffice();

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

}
