<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Title;
use PHPUnit\Framework\TestCase;

class TitleTest extends TestCase
{
    protected function getTitle($id, $language = "en-US"): Title
    {
        $config = new Config();
        $config->language = $language;
        //$config->cachedir = realpath(dirname(__FILE__) . '/cache') . '/';
        //$config->usezip = false;
        //$config->cache_expire = 259200;

        return new Title($id, $config);
    }

    public function testMainUrl()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('https://www.imdb.com/title/tt0133093/', $title->mainUrl());
    }

    /***************************************[ Title & Original Title ]***************************************/

    public function testTitle()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('The Matrix', $title->title());
    }

    public function testTitleReturnNullIfNoOriginalTitle()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertNull($title->originalTitle());
    }

    public function testTitleWithOriginalTitle()
    {
        $title = $this->getTitle('0087544'); // Nausicaä of the Valley of the Wind
        $this->assertEquals('Kaze no tani no Naushika', $title->originalTitle());
    }

    public function testTitleNonEnglishTitleUsesEnglishTitle()
    {
        $title = $this->getTitle('0087544'); // Nausicaä of the Valley of the Wind
        $this->assertEquals('Nausicaä of the Valley of the Wind', $title->title());
    }

    public function testTitleRemovesHtmlEntities()
    {
        $title = $this->getTitle('0103074'); // Thelma & Louise
        $this->assertEquals('Thelma & Louise', $title->title());
    }

    public function testTitleInDifferentLanguage()
    {
        $title = $this->getTitle('3110958', 'de-DE'); // Now You See Me 2
        $this->assertEquals('Die Unfassbaren 2', $title->title());
        $this->assertEquals('Now You See Me 2', $title->originalTitle());
    }

    public function testTitleEpisodeTitle()
    {
        $title = $this->getTitle('0579539'); // "Firefly" The Train Job
        $this->assertEquals('"Firefly" The Train Job', $title->title());
    }

    /***************************************[ Types ]***************************************/

    public function testTypeMustReturnMovie()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('Movie', $title->type());
    }

    public function testTypeMustReturnTVSeries()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $this->assertEquals('TV Series', $title->type());
    }

    public function testTypeMustReturnTVMovie()
    {
        $title = $this->getTitle("0284717"); // Crociati
        $this->assertEquals('TV Movie', $title->type());
    }

    public function testTypeMustReturnTVSpecial()
    {
        $title = $this->getTitle("5258960"); // Jochem Myjer: Even geduld aub
        $this->assertEquals('TV Special', $title->type());
    }

    public function testTypeMustReturnTVEpisode()
    {
        $title = $this->getTitle("0579539"); // Firefly: The Train Job
        $this->assertEquals('TV Episode', $title->type());
    }

    public function testTypeMustReturnTVMiniSeries()
    {
        $title = $this->getTitle("10048342"); // The Queen's Gambit
        $this->assertEquals('TV Mini Series', $title->type());
    }

    public function testTypeMustReturnVideoGame()
    {
        $title = $this->getTitle("1799527"); // Doom
        $this->assertEquals('Video Game', $title->type());
    }

    public function testTypeMustReturnVideo()
    {
        $title = $this->getTitle("0149937"); // Bottom Live
        $this->assertEquals('Video', $title->type());
    }

    /***************************************[ Year ]***************************************/

    public function testYearForAMovie()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals(1999, $title->year());
        // Film has no range, so end year is the same as year
        $this->assertEquals(1999, $title->endYear());
    }

    public function testYearForATVSeries()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $this->assertEquals(2002, $title->year());
        $this->assertEquals(2008, $title->endYear());
    }

    public function testYearReturnNullIfNoData()
    {
        $title = $this->getTitle("9916210"); // Rumpole of the Bailey
        $this->assertNull($title->year());
        $this->assertNull($title->endYear());
    }

    /***************************************[ Runtime ]***************************************/

    public function testRuntime()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals(136, $title->runtime());
    }

    public function testRuntimeTVSeries()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $this->assertEquals(59, $title->runtime());
    }

    public function testRuntimeTVEpisode()
    {
        $title = $this->getTitle('0579539'); // "Firefly" The Train Job
        $this->assertEquals(42, $title->runtime());
    }

    /***************************************[ Photo ]***************************************/

    public function testPhoto()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->photo();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BNzQzOTk3OTAtNDQ0Zi00ZTVkLWI0MTEtMDllZjNkYzNjNTc4L2ltYWdlXkEyXkFqcGdeQXVyNjU0OTQ0OTY@._V1_QL75_UX190_CR0,2,190,281_.jpg', $result['thumbnail']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BNzQzOTk3OTAtNDQ0Zi00ZTVkLWI0MTEtMDllZjNkYzNjNTc4L2ltYWdlXkEyXkFqcGdeQXVyNjU0OTQ0OTY@.jpg', $result['original']);
    }

    public function testPhotoReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $result = $title->photo();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Tagline ]***************************************/

    public function testTagline()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals("Free your mind", $title->tagline());
    }

    public function testTaglines()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->taglines();

        $this->assertIsArray($result);
        $this->assertCount(15, $result);
        $this->assertTrue(in_array($title->tagline(), $result));
        $this->assertEquals("744", strlen(implode(', ', $result)));
    }

    public function testTaglineReturnNullIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $this->assertNull($title->tagline());
    }

    public function testTaglinesAll()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $result = $title->taglines();

        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        $this->assertEquals("237", strlen(implode(', ', $result)));
        $this->assertEquals('A new case begins... (second season)', $result[0]);
        $this->assertEquals('Rules change. The game remains the same. (third season)', $result[1]);
        $this->assertEquals('No corner left behind. (fourth season)', $result[2]);
        $this->assertEquals('Listen carefully (first season)', $result[3]);
        $this->assertEquals('All in the game. (fifth season)', $result[4]);
        $this->assertEquals('Read between the lines (season five)', $result[5]);
    }

    public function testTaglinesAllReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $result = $title->taglines();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Genres ]***************************************/

    public function testGenres()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->genres();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Action', $result[0]);
        $this->assertEquals('Sci-Fi', $result[1]);
    }

    /***************************************[ Languages ]***************************************/

    public function testLanguagesOneLanguage()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->languages();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('English', $result[0]);
    }

    public function testLanguagesDetailedOneLanguage()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->languagesDetailed();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        $this->assertEquals('English', $result[0]['name']);
        $this->assertEquals('en', $result[0]['code']);
        $this->assertEquals('', $result[0]['comment']);
    }

    public function testLanguagesMultipleLanguages()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $result = $title->languages();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $this->assertEquals('English', $result[0]);
        $this->assertEquals('Greek', $result[1]);
        $this->assertEquals('Mandarin', $result[2]);
        $this->assertEquals('Spanish', $result[3]);
    }

    public function testLanguagesDetailedMultipleLanguages()
    {
        $title = $this->getTitle("0306414"); // The Wire
        $result = $title->languagesDetailed();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $this->assertEquals([
            [
                'name' => 'English',
                'code' => 'en',
                'comment' => ''
            ],
            [
                'name' => 'Greek',
                'code' => 'el',
                'comment' => ''
            ],
            [
                'name' => 'Mandarin',
                'code' => 'cmn',
                'comment' => ''
            ],
            [
                'name' => 'Spanish',
                'code' => 'es',
                'comment' => ''
            ]
        ], $title->languagesDetailed());
    }

    public function testLanguagesReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("0171236"); // The Lor Girl (Dokhtare Lor ya irane druz va emruz)
        $result = $title->languages();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Countries ]***************************************/

    public function testCountries()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->countries();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('United States', $result[0]);
        $this->assertEquals('Australia', $result[1]);
    }

    public function testCountriesDetailed()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->countriesDetailed();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals('United States', $result[0]['name']);
        $this->assertEquals('us', $result[0]['code']);

        $this->assertEquals('Australia', $result[1]['name']);
        $this->assertEquals('au', $result[1]['code']);
    }

    public function testCountriesReturnsEmptyArrayIfNoCountry()
    {
        $title = $this->getTitle("7332864"); // Der Schuldschein des Pandola (Marineleutnant von Brinken. I)
        $result = $title->countries();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Rating & Votes ]***************************************/

    public function testRating()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('8.7', $title->rating());
    }

    public function testRatingReturnNullIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $this->assertNull($title->rating());
    }

    public function testVotes()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $votes = $title->votes();

        $this->assertIsInt($votes);
        $this->assertGreaterThan(1700000, $votes);
        $this->assertLessThan(2000000, $votes);
    }

    public function testVotesReturnNullIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $this->assertNull($title->votes());
    }

    /***************************************[ Colors ]***************************************/

    public function testColorsOneColor()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->colors();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Color', $result[0]);
    }

    public function testColorsMultipleColors()
    {
        $title = $this->getTitle("0108052"); // Schindler's List
        $result = $title->colors();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Black and White', $result[0]);
        $this->assertEquals('Color', $result[1]);
    }

    public function testColorsReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("9843312"); // Ninja Ko
        $result = $title->colors();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Sounds ]***************************************/

    public function testSounds()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->sounds();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('Dolby Digital', $result[0]);
        $this->assertEquals('SDDS', $result[1]);
        $this->assertEquals('Dolby Atmos', $result[2]);
    }

    public function testSoundsReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $result = $title->sounds();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Aspect Ratio ]***************************************/

    public function testAspectRatio()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $this->assertEquals('2.39 : 1', $title->aspectRatio());
    }

    public function testAspectRatioReturnNullIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $this->assertNull($title->aspectRatio());
    }

    /***************************************[ Locations ]***************************************/

    public function testLocations()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->locations();

        $this->assertIsArray($result);
        $this->assertGreaterThan(20, count($result));
        $this->assertTrue(in_array('Nashville, Tennessee, USA', $result));
        $this->assertTrue(in_array('Sydney, New South Wales, Australia', $result));
        $this->assertTrue(in_array('Redfern, Sydney, New South Wales, Australia', $result));
    }

    public function testLocationsReturnEmptyArrayIfNoData()
    {
        $title = $this->getTitle("7618100"); // Untitled Star Wars Trilogy: Episode III
        $result = $title->locations();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }

    /***************************************[ Keywords ]***************************************/

    public function testKeywords()
    {
        $title = $this->getTitle("0133093"); // The Matrix
        $result = $title->keywords();

        $this->assertIsArray($result);
        $this->assertGreaterThan(340, count($result));
        $this->assertTrue(in_array('artificial reality', $result));
        $this->assertTrue(in_array('truth', $result));
        $this->assertTrue(in_array('human machine relationship', $result));
    }

    /***************************************[ MPAA ]***************************************/

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

}
