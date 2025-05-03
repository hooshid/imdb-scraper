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
    }
}
