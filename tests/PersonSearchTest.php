<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\PersonSearch;
use PHPUnit\Framework\TestCase;

class PersonSearchTest extends TestCase
{
    protected function getPersonSearch($language = "en-US"): PersonSearch
    {
        $config = new Config();
        $config->language = $language;

        return new PersonSearch($config);
    }

    /***************************************[ Search ]***************************************/

    public function testSearch()
    {
        $person = $this->getPersonSearch(); // Johnny Depp
        $result = $person->search(['name' => 'Johnny Depp']);
        $this->assertIsArray($result);
        $this->assertIsArray($result[0]['photo']);
        $this->assertEquals('1', $result[0]['index']);
        $this->assertEquals('nm0000136', $result[0]['id']);
        $this->assertEquals('Johnny Depp', $result[0]['name']);
        $this->assertEquals('Actor', $result[0]['job']);
        $this->assertGreaterThan(250, strlen($result[0]['bio']));
    }
}
