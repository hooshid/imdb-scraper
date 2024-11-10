<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    protected function getPerson($id, $language = "en-US"): Name
    {
        $config = new Config();
        $config->language = $language;

        return new Name($id, $config);
    }

    public function testMainUrl()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $this->assertEquals('https://www.imdb.com/name/nm0000134/', $person->mainUrl());
    }

    /***************************************[ Full Name ]***************************************/

    public function testFullName()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $this->assertEquals('Robert De Niro', $person->fullName());
    }

    /***************************************[ Photo ]***************************************/

    public function testPhoto()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->photo();

        $this->assertIsArray($result);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMjAwNDU3MzcyOV5BMl5BanBnXkFtZTcwMjc0MTIxMw@@._V1_.jpg', $result['original']);
    }

    public function testPhotoReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $result = $person->photo();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Birth ]***************************************/

    public function testBirth()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
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
        $person = $this->getPerson("nm0275981"); // Karin Field
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
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $result = $person->birth();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Death ]***************************************/

    public function testDeath()
    {
        $person = $this->getPerson("nm0908094"); // Paul Walker
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
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $result = $person->death();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    public function testDeathReturnEmptyArrayIfNoValidData()
    {
        $person = $this->getPerson("nm0711405"); // Rasmus Rasmussen
        $result = $person->death();

        $this->assertIsArray($result);
        $this->assertCount(7, $result);
        $this->assertNotNull($result['year']);
    }

    /***************************************[ Birth Name ]***************************************/

    public function testBirthName()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $this->assertEquals('Robert Anthony De Niro Jr.', $person->birthName());
    }

    public function testReturnNullIfNoData()
    {
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $this->assertNull($person->birthName());
    }

    /***************************************[ Nick Names ]***************************************/

    public function testNickNames()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->nickNames();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals('Bobby Milk, Kid Monroe, Bob, Bobby D', implode(', ', $result));
    }

    public function testNickNamesReturnEmptyArrayIfNoData()
    {
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $result = $person->nickNames();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    public function testAKANames()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->akaNames();

        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        $this->assertEquals('Robert De Niro Jr., Robâto De Nîro, Bobby DeNiro, Robert DeNiro, Robert Denero', implode(', ', $result));
    }

    public function testRank()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->rank();

        $this->assertIsInt($result['current_rank']);
        $this->assertIsString($result['change_direction']);
        $this->assertIsInt($result['difference']);
    }


    /***************************************[ Body Height ]***************************************/

    public function testBodyHeight()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->bodyHeight();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals("5' 9½\"", $result['imperial']);
        $this->assertEquals('1.77 m', $result['metric']);
        $this->assertEquals('177', $result['metric_cm']);
    }

    public function testBodyHeightReturnsEmptyArrayIfNoData()
    {
        $person = $this->getPerson("nm0830093"); // Gillian Stein
        $result = $person->bodyHeight();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Bio ]***************************************/

    public function testBio()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->bio();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertGreaterThan(1000, strlen($result[0]['text']));
    }

    public function testProfessions()
    {
        $person = $this->getPerson("nm0000134"); // Robert De Niro
        $result = $person->professions();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals("Actor, Producer, Director", implode(", ", $result));
    }
}
