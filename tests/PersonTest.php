<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    protected function getPerson($id, $language = "en-US"): Person
    {
        $config = new Config();
        $config->language = $language;
        //$config->cachedir = realpath(dirname(__FILE__) . '/cache') . '/';
        //$config->usezip = false;
        //$config->cache_expire = 259200;

        return new Person($id, $config);
    }

    public function testMainUrl()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $this->assertEquals('https://www.imdb.com/name/nm0000134/', $person->mainUrl());
    }

    /***************************************[ Full Name ]***************************************/

    public function testFullName()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $this->assertEquals('Robert De Niro', $person->fullName());
    }

    /***************************************[ Photo ]***************************************/

    public function testPhoto()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $result = $person->photo();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMjAwNDU3MzcyOV5BMl5BanBnXkFtZTcwMjc0MTIxMw@@._V1_QL75_UY207_CR9,0,140,207_.jpg', $result['thumbnail']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMjAwNDU3MzcyOV5BMl5BanBnXkFtZTcwMjc0MTIxMw@@.jpg', $result['original']);
    }

    public function testPhotoReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->photo();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Birth ]***************************************/

    public function testBirth()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $result = $person->birth();

        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        $this->assertEquals('17', $result['day']);
        $this->assertEquals('August', $result['month']);
        $this->assertEquals('8', $result['mon']);
        $this->assertEquals('1943', $result['year']);
        $this->assertEquals('1943-08-17', $result['date']);
        $this->assertEquals('New York City, New York, USA', $result['place']);
    }

    public function testBirthJustYearFilled()
    {
        $person = $this->getPerson("0275981"); // Karin Field
        $result = $person->birth();

        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        $this->assertNull($result['day']);
        $this->assertNull($result['month']);
        $this->assertNull($result['mon']);
        $this->assertEquals('1944', $result['year']);
        $this->assertNull($result['date']);
        $this->assertEquals('Hamburg, Germany', $result['place']);
    }

    public function testBirthReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->birth();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Death ]***************************************/

    public function testDeath()
    {
        $person = $this->getPerson("0908094"); // Paul Walker
        $result = $person->death();

        $this->assertIsArray($result);
        $this->assertCount(7, $result);
        $this->assertEquals('30', $result['day']);
        $this->assertEquals('November', $result['month']);
        $this->assertEquals('11', $result['mon']);
        $this->assertEquals('2013', $result['year']);
        $this->assertEquals('2013-11-30', $result['date']);
        $this->assertEquals('Valencia, Santa Clarita, California, USA', $result['place']);
        $this->assertEquals('car accident', $result['cause']);
    }

    public function testDeathReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->death();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Birth Name ]***************************************/

    public function testBirthName()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $this->assertEquals('Robert Anthony De Niro Jr.', $person->birthName());
    }

    public function testReturnNullIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $this->assertNull($person->birthName());
    }

    /***************************************[ Nick Names ]***************************************/

    public function testNickNames()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $result = $person->nickNames();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals('Bobby Milk, Kid Monroe, Bob, Bobby D', implode(', ', $result));
    }

    public function testNickNamesReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->nickNames();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Body Height ]***************************************/

    public function testBodyHeight()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $result = $person->bodyHeight();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals("5' 9½\"", $result['imperial']);
        $this->assertEquals('1.75 m', $result['metric']);
        $this->assertEquals('175', $result['metric_cm']);
    }

    public function testBodyHeightReturnsEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->bodyHeight();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Bio ]***************************************/

    public function testBio()
    {
        $person = $this->getPerson("0000134"); // Robert De Niro
        $result = $person->bio();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals("3297", strlen($result[0]['text']));
    }

    public function testBioReturnsEmptyArrayIfNoData()
    {
        $person = $this->getPerson("0830093"); // Gillian Stein
        $result = $person->bio();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }
}
