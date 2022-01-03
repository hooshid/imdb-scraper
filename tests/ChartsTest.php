<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Charts;
use PHPUnit\Framework\TestCase;

class ChartsTest extends TestCase
{
    protected function getCharts($language = "en-US"): Charts
    {
        $config = new Config();
        $config->language = $language;

        return new Charts($config);
    }

    public function testGetChartsBoxOffice()
    {
        $charts = $this->getCharts();
        $boxOffice = $charts->getChartsBoxOffice();

        $this->assertIsArray($boxOffice);
        $this->assertTrue(count($boxOffice) >= 9);
        $this->assertTrue(count($boxOffice) < 11);

        foreach ($boxOffice as $film) {
            $this->assertIsArray($film);
            $this->assertCount(5, $film);
            $this->assertTrue(strlen($film['id']) >= 9 and strlen($film['id']) <= 10);
            $this->assertTrue(is_numeric($film['weekend']));
            $this->assertTrue(is_numeric($film['gross']));
        }
    }

}
