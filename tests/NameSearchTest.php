<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\NameSearch;
use PHPUnit\Framework\TestCase;

class NameSearchTest extends TestCase
{
    protected function getNameSearch($language = "en-US"): NameSearch
    {
        $config = new Config();
        $config->language = $language;

        return new NameSearch($config);
    }

    /***************************************[ Search ]***************************************/

    public function testSearch()
    {
        $name = $this->getNameSearch();
        $result = $name->search(['name' => 'Depp']);

        $this->assertIsArray($result);
        $this->assertCount(25, $result);

        // 1. Johnny Depp
        $this->assertIsArray($result[0]['photo']);
        $this->assertEquals(1, $result[0]['index']);
        $this->assertEquals('nm0000136', $result[0]['id']);
        $this->assertEquals('Johnny Depp', $result[0]['name']);
        $this->assertEquals('Actor, Producer, Director', $result[0]['job']);
        $this->assertGreaterThan(250, strlen($result[0]['bio']));

        // 3. Lori A. Depp
        $this->assertNull($result[2]['photo']);
        $this->assertEquals(3, $result[2]['index']);
        $this->assertEquals('nm0220129', $result[2]['id']);
        $this->assertEquals('Lori A. Depp', $result[2]['name']);
    }
}
