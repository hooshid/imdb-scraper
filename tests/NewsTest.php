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
        $this->assertNotNull($list[0]['source_url']);
        $this->assertNotNull($list[0]['source_home_url']);
        $this->assertNotNull($list[0]['source_label']);
        $this->assertNotNull($list[0]['plain_html']);
        $this->assertNotNull($list[0]['plain_text']);
        $this->assertIsArray($list[0]['image']);
    }
}
