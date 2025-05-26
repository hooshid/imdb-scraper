<?php

use Hooshid\ImdbScraper\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testPerson()
    {
        // Robert De Niro
        $name = new Name("nm0000134");
        $name->spouses();
        $name->salaries();
        $name->images(25);
        $name->videos(25);
        $name->news(25);
        $person = $name->full();

        $this->assertEquals('nm0000134', $person['imdb_id']);
        $this->assertEquals('https://www.imdb.com/name/nm0000134/', $person['main_url']);
        $this->assertNull($person['canonical_id']);
        $this->assertEquals('Robert De Niro', $person['full_name']);
        $this->assertIsArray($person['image']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMjAwNDU3MzcyOV5BMl5BanBnXkFtZTcwMjc0MTIxMw@@._V1_.jpg', $person['image']['url']);

        $this->assertIsArray($person['rank']);
        $this->assertIsInt($person['rank']['current_rank']);
        $this->assertIsString($person['rank']['change_direction']);
        $this->assertIsInt($person['rank']['difference']);

        $this->assertIsInt($person['age']);

        $this->assertCount(5, $person['birth']);
        $this->assertEquals(17, $person['birth']['day']);
        $this->assertEquals(8, $person['birth']['month']);
        $this->assertEquals(1943, $person['birth']['year']);
        $this->assertEquals('1943-08-17', $person['birth']['date']);
        $this->assertEquals('New York City, New York, USA', $person['birth']['place']);

        $this->assertNull($person['death']);

        $this->assertEquals('Robert Anthony De Niro Jr.', $person['birth_name']);

        $this->assertIsArray($person['nick_names']);
        $this->assertCount(4, $person['nick_names']);
        $this->assertEquals('Bobby Milk, Kid Monroe, Bob, Bobby D', implode(', ', $person['nick_names']));

        $this->assertIsArray($person['aka_names']);
        $this->assertCount(5, $person['aka_names']);
        $this->assertEquals('Robert De Niro Jr., Robâto De Nîro, Bobby DeNiro, Robert DeNiro, Robert Denero', implode(', ', $person['aka_names']));

        $this->assertIsArray($person['body_height']);
        $this->assertCount(3, $person['body_height']);
        $this->assertEquals("5′ 9½″", $person['body_height']['imperial']);
        $this->assertEquals('1.77 m', $person['body_height']['metric']);
        $this->assertEquals(177, $person['body_height']['metric_cm']);

        $this->assertIsArray($person['bio']);
        $this->assertCount(2, $person['bio']);
        $this->assertGreaterThan(1000, strlen($person['bio'][0]['text']));
        $this->assertEquals('Pedro Borges', $person['bio'][0]['author']);

        $this->assertIsArray($person['professions']);
        $this->assertCount(3, $person['professions']);
        $this->assertEquals("Actor, Producer, Director", implode(", ", $person['professions']));

        $this->assertIsArray($person['spouses']);
        $this->assertCount(8, $person['spouses'][0]);
        $this->assertEquals('nm2984460', $person['spouses'][0]['id']);
        $this->assertEquals('Grace Hightower', $person['spouses'][0]['name']);
        $this->assertEquals(2, $person['spouses'][0]['children']);
        $this->assertIsBool($person['spouses'][0]['current']);

        $this->assertIsArray($person['salaries']);
        $this->assertCount(6, $person['salaries'][0]);

        $this->assertIsArray($person['images']);
        $this->assertCount(25, $person['images']);

        $this->assertIsArray($person['videos']);
        $this->assertCount(25, $person['videos']);

        $this->assertIsArray($person['news']);
        $this->assertCount(25, $person['news']);
    }

    public function testPersonDied()
    {
        // Paul Walker
        $name = new Name("nm0908094");

        $this->assertEquals(40, $name->age());

        $death = $name->death();
        $this->assertCount(6, $death);
        $this->assertEquals(30, $death['day']);
        $this->assertEquals(11, $death['month']);
        $this->assertEquals(2013, $death['year']);
        $this->assertEquals('2013-11-30', $death['date']);
        $this->assertEquals('Valencia, Santa Clarita, California, USA', $death['place']);
        $this->assertEquals('car accident', $death['cause']);
    }

    public function testPersonMissingInfos()
    {
        // Gillian Stein
        $name = new Name("nm0830093");
        $person = $name->full();

        $this->assertEquals('nm0830093', $person['imdb_id']);
        $this->assertEquals('https://www.imdb.com/name/nm0830093/', $person['main_url']);
        $this->assertNull($person['canonical_id']);
        $this->assertEquals('Gillian Stein', $person['full_name']);
        $this->assertNull($person['image']);
        $this->assertNull($person['rank']);
        $this->assertNull($person['age']);
        $this->assertNull($person['birth']);
        $this->assertNull($person['death']);
        $this->assertNull($person['birth_name']);
        $this->assertNull($person['nick_names']);
        $this->assertNull($person['body_height']);
    }

    public function testPersonMissingInfos2()
    {
        // Rasmus Rasmussen
        $name = new Name("nm0711405");
        $person = $name->full();

        $this->assertEquals('nm0711405', $person['imdb_id']);
        $this->assertEquals('https://www.imdb.com/name/nm0711405/', $person['main_url']);
        $this->assertNull($person['canonical_id']);
        $this->assertEquals('Rasmus Rasmussen', $person['full_name']);
        $this->assertIsArray($person['image']);
        $this->assertNull($person['rank']);
        $this->assertEquals(70, $person['age']);

        $this->assertCount(5, $person['birth']);
        $this->assertNull($person['birth']['day']);
        $this->assertNull($person['birth']['month']);
        $this->assertEquals(1862, $person['birth']['year']);
        $this->assertNull($person['birth']['date']);
        $this->assertNull($person['birth']['place']);

        $this->assertCount(6, $person['death']);
        $this->assertNull($person['death']['day']);
        $this->assertNull($person['death']['month']);
        $this->assertEquals(1932, $person['death']['year']);
        $this->assertNull($person['death']['date']);
        $this->assertNull($person['death']['place']);
        $this->assertNull($person['death']['cause']);

        $this->assertNull($person['birth_name']);
        $this->assertNull($person['nick_names']);
        $this->assertNull($person['aka_names']);
        $this->assertNull($person['body_height']);
    }
}
