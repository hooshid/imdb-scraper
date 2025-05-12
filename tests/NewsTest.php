<?php

use Hooshid\ImdbScraper\News;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase
{
    public function testTVNews()
    {
        $video = new News();
        $list = $video->newsList('TV', 50);

        $this->assertIsArray($list);
        $this->assertCount(50, $list);

        $this->assertStringStartsWith('ni', $list[0]['id']);
        $this->assertNotNull($list[0]['title']);
        $this->assertNotNull($list[0]['date']);
        $this->assertNotNull($list[0]['sourceUrl']);
        $this->assertNotNull($list[0]['sourceHomeUrl']);
        $this->assertNotNull($list[0]['sourceLabel']);
        $this->assertNotNull($list[0]['plainHtml']);
        $this->assertNotNull($list[0]['plainText']);
        $this->assertIsArray($list[0]['image']);
    }
}
