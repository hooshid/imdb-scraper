<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Title;
use PHPUnit\Framework\TestCase;

class TitleTest extends TestCase
{
    public function testMovie()
    {
        $title = new Title('tt0133093');
        $data = $title->full(['keywords', 'locations', 'sounds', 'colors', 'aspect_ratio', 'cameras', 'videos']);

        $this->assertEquals('tt0133093', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt0133093/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals('The Matrix', $data['title']);
        $this->assertNull($data['original_title']);
        $this->assertEquals('Movie', $data['type']);
        $this->assertEquals(1999, $data['year']);
        $this->assertNull($data['end_year']);

        $this->assertIsArray($data['image']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BN2NmN2VhMTQtMDNiOS00NDlhLTliMjgtODE2ZTY0ODQyNDRhXkEyXkFqcGc@._V1_.jpg', $data['image']['url']);
        $this->assertEquals(2100, $data['image']['width']);
        $this->assertEquals(3156, $data['image']['height']);

        $this->assertEquals(8.7, $data['ratings']['rating']);
        $this->assertGreaterThan(2000000, $data['ratings']['votes']);

        $this->assertIsInt($data['rank']['current_rank']);
        $this->assertIsString($data['rank']['change_direction']);
        $this->assertIsInt($data['rank']['difference']);

        $this->assertIsArray($data['languages']);
        $this->assertCount(1, $data['languages']);
        $this->assertEquals('en', $data['languages'][0]['code']);
        $this->assertEquals('English', $data['languages'][0]['name']);

        $this->assertIsArray($data['countries']);
        $this->assertCount(2, $data['countries']);
        $this->assertEquals('US', $data['countries'][0]['code']);
        $this->assertEquals('United States', $data['countries'][0]['name']);
        $this->assertEquals('AU', $data['countries'][1]['code']);
        $this->assertEquals('Australia', $data['countries'][1]['name']);

        $this->assertEquals(136, $data['runtime']);
        $this->assertIsArray($data['runtimes']);

        $this->assertIsArray($data['taglines']);
        $this->assertCount(15, $data['taglines']);
        $this->assertEquals('Free your mind', $data['taglines'][0]);

        $this->assertIsArray($data['keywords']);
        $this->assertCount(403, $data['keywords']);
        $this->assertEquals('artificial reality', $data['keywords'][0]);
        $this->assertEquals('war with machines', $data['keywords'][1]);
        $this->assertEquals('simulated reality', $data['keywords'][2]);
        $this->assertEquals('dystopia', $data['keywords'][3]);

        $this->assertIsArray($data['locations']);
        $this->assertCount(22, $data['locations']);
        $this->assertEquals('Nashville, Tennessee, USA', $data['locations'][0]['real']);
        $this->assertEquals('exterior scenes: skyline in opening Trinity rooftop chase', $data['locations'][0]['scenes'][0]);

        $this->assertIsArray($data['sounds']);
        $this->assertCount(4, $data['sounds']);
        $this->assertEquals('DTS', $data['sounds'][0]['value']);
        $this->assertEquals('Dolby Digital', $data['sounds'][1]['value']);
        $this->assertEquals('SDDS', $data['sounds'][2]['value']);
        $this->assertEquals('Dolby Atmos', $data['sounds'][3]['value']);

        $this->assertIsArray($data['colors']);
        $this->assertCount(1, $data['colors']);
        $this->assertEquals('Color', $data['colors'][0]['value']);

        $this->assertIsArray($data['aspect_ratio']);
        $this->assertCount(2, $data['aspect_ratio']);
        $this->assertEquals('2.20 : 1', $data['aspect_ratio'][0]['value']);
        $this->assertEquals('2.39 : 1', $data['aspect_ratio'][1]['value']);

        $this->assertIsArray($data['cameras']);
        $this->assertCount(4, $data['cameras']);
        $this->assertEquals('Arriflex 435, Panavision Primo Lenses', $data['cameras'][0]['value']);

        $this->assertIsArray($data['videos']);
        $this->assertGreaterThanOrEqual(18, count($data['videos']));
    }

    public function testMovie2()
    {
        $title = new Title('tt7618100');
        $data = $title->full(['keywords', 'locations', 'sounds', 'colors', 'aspect_ratio', 'cameras']);

        $this->assertEquals('tt7618100', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt7618100/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals('Untitled Star Wars Trilogy: Episode III', $data['title']);
        $this->assertNull($data['original_title']);
        $this->assertEquals('Movie', $data['type']);
        $this->assertNull($data['year']);
        $this->assertNull($data['end_year']);
        $this->assertNull($data['image']);
        $this->assertNull($data['ratings']['rating']);
        $this->assertEquals(0, $data['ratings']['votes']);
        $this->assertNull($data['rank']);
        $this->assertNull($data['runtimes']);
        $this->assertNull($data['taglines']);
        $this->assertIsArray($data['keywords']);
        $this->assertCount(8, $data['keywords']);
        $this->assertNull($data['locations']);
        $this->assertNull($data['sounds']);
        $this->assertCount(1, $data['colors']);
        $this->assertNull($data['aspect_ratio']);
        $this->assertNull($data['cameras']);
    }

    public function testMovie3()
    {
        $title = new Title('tt9843312');
        $data = $title->full(['keywords', 'colors', 'mpaas','videos']);

        $this->assertEquals('tt9843312', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt9843312/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals('Ninja Ko', $data['title']);
        $this->assertEquals('Ninja Ko, the Origami Master', $data['original_title']);
        $this->assertEquals('Video', $data['type']);
        $this->assertEquals(1990, $data['year']);
        $this->assertNull($data['end_year']);
        $this->assertNull($data['rank']);
        $this->assertIsArray($data['keywords']);
        $this->assertNull($data['colors']);
        $this->assertNull($data['mpaas']);
        $this->assertNull($data['videos']);
    }

    public function testMovie4()
    {
        $title = new Title('tt0108052');
        $data = $title->full(['colors']);

        $this->assertEquals('tt0108052', $data['imdb_id']);
        $this->assertEquals("Schindler's List", $data['title']);
        $this->assertEquals('Movie', $data['type']);

        $this->assertIsArray($data['colors']);
        $this->assertCount(2, $data['colors']);
        $this->assertEquals('Black and White', $data['colors'][0]['value']);
        $this->assertEquals('Color', $data['colors'][1]['value']);
    }

    public function testMovie5()
    {
        $title = new Title('tt0087544');
        $data = $title->full(['locations', 'cameras']);

        $this->assertEquals('tt0087544', $data['imdb_id']);
        $this->assertEquals("Nausicaä of the Valley of the Wind", $data['title']);
        $this->assertEquals("Kaze no tani no Naushika", $data['original_title']);
        $this->assertEquals('Movie', $data['type']);

        $this->assertNull($data['locations']);
        $this->assertNull($data['cameras']);
    }

    public function testMovieInDifferentLanguage()
    {
        $config = new Config();
        $config->useLocalization = true;
        $config->country = 'DE';
        $config->language = 'de-DE';
        $title = new Title('tt3110958', $config);
        $data = $title->full();

        $this->assertEquals('tt3110958', $data['imdb_id']);
        $this->assertEquals('Die Unfassbaren 2', $data['title']);
        $this->assertEquals('Now You See Me 2', $data['original_title']);
        $this->assertEquals('Movie', $data['type']);
    }

    public function testSeries()
    {
        $title = new Title('tt0306414');
        $data = $title->full(['keywords', 'videos']);

        $this->assertEquals('tt0306414', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt0306414/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals('The Wire', $data['title']);
        $this->assertNull($data['original_title']);
        $this->assertEquals('TV Series', $data['type']);
        $this->assertEquals(2002, $data['year']);
        $this->assertEquals(2008, $data['end_year']);

        $this->assertIsArray($data['image']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BZWYyNmRhYjktNjBhNC00M2NhLWEzYmMtZDYwNmIyZTRiZWMzXkEyXkFqcGc@._V1_.jpg', $data['image']['url']);
        $this->assertEquals(960, $data['image']['width']);
        $this->assertEquals(1440, $data['image']['height']);

        $this->assertIsArray($data['languages']);
        $this->assertCount(4, $data['languages']);
        $this->assertEquals('en', $data['languages'][0]['code']);
        $this->assertEquals('English', $data['languages'][0]['name']);

        $this->assertIsArray($data['countries']);
        $this->assertCount(1, $data['countries']);
        $this->assertEquals('US', $data['countries'][0]['code']);
        $this->assertEquals('United States', $data['countries'][0]['name']);

        $this->assertEquals(60, $data['runtime']);
        $this->assertIsArray($data['runtimes']);

        $this->assertIsArray($data['taglines']);
        $this->assertCount(6, $data['taglines']);
        $this->assertEquals("237", strlen(implode(', ', $data['taglines'])));
        $this->assertEquals('A new case begins... (second season)', $data['taglines'][0]);
        $this->assertEquals('Rules change. The game remains the same. (third season)', $data['taglines'][1]);
        $this->assertEquals('No corner left behind. (fourth season)', $data['taglines'][2]);
        $this->assertEquals('Listen carefully (first season)', $data['taglines'][3]);
        $this->assertEquals('All in the game. (fifth season)', $data['taglines'][4]);
        $this->assertEquals('Read between the lines (season five)', $data['taglines'][5]);

        $this->assertIsArray($data['keywords']);
        $this->assertCount(63, $data['keywords']);
        $this->assertEquals('baltimore maryland', $data['keywords'][0]);

        $this->assertIsArray($data['videos']);
        $this->assertGreaterThanOrEqual(10, count($data['videos']));
    }

    public function testTVMiniSeries()
    {
        $title = new Title('tt10048342');
        $data = $title->full();

        $this->assertEquals('tt10048342', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt10048342/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals("The Queen's Gambit", $data['title']);
        $this->assertNull($data['original_title']);
        $this->assertEquals('TV Mini Series', $data['type']);
        $this->assertEquals(2020, $data['year']);
        $this->assertEquals(2020, $data['end_year']);
    }

    public function testTVEpisode()
    {
        $title = new Title('tt0579539');
        $data = $title->full();

        $this->assertEquals('tt0579539', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt0579539/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals("The Train Job", $data['title']);
        $this->assertNull($data['original_title']);
        $this->assertEquals('TV Episode', $data['type']);
        $this->assertEquals(2002, $data['year']);
        $this->assertNull($data['end_year']);
    }

    public function testTVMovie()
    {
        $title = new Title('tt0284717');
        $data = $title->full();

        $this->assertEquals('tt0284717', $data['imdb_id']);
        $this->assertEquals('https://www.imdb.com/title/tt0284717/', $data['main_url']);
        $this->assertNull($data['canonical_id']);
        $this->assertEquals('The Crusaders', $data['title']);
        $this->assertEquals('Crociati', $data['original_title']);
        $this->assertEquals('TV Movie', $data['type']);
        $this->assertEquals(2001, $data['year']);
        $this->assertNull($data['end_year']);
    }

    /*
    public function testGenres()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->genres();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Action', $result[0]);
        $this->assertEquals('Sci-Fi', $result[1]);
    }

    public function testMpaa()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->mpaa();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('United States', $result);
        $this->assertEquals('R', $result['United States']);
    }

    public function testMpaaReason()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('Rated R for sci-fi violence and brief language', $title->mpaaReason());
    }
    */
}
