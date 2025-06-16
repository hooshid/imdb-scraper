<?php

use Hooshid\ImdbScraper\Trailers;
use PHPUnit\Framework\TestCase;

class TrailersTest extends TestCase
{
    public function testRecentVideos()
    {
        $trailers = new Trailers();
        $list = $trailers->recentVideos(50);

        $this->assertIsArray($list);
        $this->assertGreaterThan(10, count($list));

        $this->assertStringStartsWith('vi', $list[0]['id']);
        $this->assertEquals("https://www.imdb.com/video/" . $list[0]['id'] . "/", $list[0]['playback_url']);
        $this->assertNotNull($list[0]['created_date']);
        $this->assertNotNull($list[0]['runtime_formatted']);
        $this->assertNotNull($list[0]['runtime_seconds']);
        $this->assertNotNull($list[0]['title']);
        $this->assertEquals("Trailer",$list[0]['content_type']);
        $this->assertIsArray($list[0]['thumbnail']);

        $this->assertIsArray($list[0]['primary_title']);
        $this->assertNotNull($list[0]['primary_title']['id']);
        $this->assertNotNull($list[0]['primary_title']['title']);
        //$this->assertNotNull($list[0]['primary_title']['release_date']);
        $this->assertIsArray($list[0]['primary_title']['image']);
    }

    public function testTrendingVideos()
    {
        $trailers = new Trailers();
        $list = $trailers->trendingVideos(50);

        $this->assertIsArray($list);
        $this->assertGreaterThan(10, count($list));

        $this->assertStringStartsWith('vi', $list[0]['id']);
        $this->assertEquals("https://www.imdb.com/video/" . $list[0]['id'] . "/", $list[0]['playback_url']);
        $this->assertNotNull($list[0]['created_date']);
        $this->assertNotNull($list[0]['runtime_formatted']);
        $this->assertNotNull($list[0]['runtime_seconds']);
        $this->assertNotNull($list[0]['title']);
        $this->assertEquals("Trailer",$list[0]['content_type']);
        $this->assertIsArray($list[0]['thumbnail']);

        $this->assertIsArray($list[0]['primary_title']);
        $this->assertNotNull($list[0]['primary_title']['id']);
        $this->assertNotNull($list[0]['primary_title']['title']);
        $this->assertNotNull($list[0]['primary_title']['release_date']);
        $this->assertIsArray($list[0]['primary_title']['image']);
    }
}
