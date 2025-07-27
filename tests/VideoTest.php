<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testVideo1()
    {
        $video = new Video();
        $vi = $video->video('vi3067265305'); // Tenet - Official Trailer

        $this->assertIsArray($vi);
        $this->assertCount(13, $vi);
        $this->assertEquals('vi3067265305', $vi['id']);
        $this->assertEquals('https://www.imdb.com/video/vi3067265305/', $vi['playback_url']);
        $this->assertEquals('2020-10-31 17:04:10', $vi['created_date']);
        $this->assertFalse($vi['is_mature']);
        $this->assertEquals('2:03', $vi['runtime_formatted']);
        $this->assertEquals(123, $vi['runtime_seconds']);
        $this->assertEquals(1.777778, $vi['video_aspect_ratio']);
        $this->assertEquals('Tenet', $vi['title']);
        $this->assertEquals('Official Trailer', $vi['description']);
        $this->assertEquals('Trailer', $vi['content_type']);

        $this->assertIsArray($vi['thumbnail']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BZWEyNGRmMDUtM2E5MS00ZTM3LTk5ZWEtNjJmYTAzNTFiNzg3XkEyXkFqcGdeQVRoaXJkUGFydHlJbmdlc3Rpb25Xb3JrZmxvdw@@._V1_.jpg', $vi['thumbnail']['url']);
        $this->assertEquals(1920, $vi['thumbnail']['width']);
        $this->assertEquals(1080, $vi['thumbnail']['height']);


        $this->assertEquals('tt6723592', $vi['primary_title']['id']);
        $this->assertEquals('Tenet', $vi['primary_title']['title']);
        $this->assertEquals('2020-09-03', $vi['primary_title']['release_date']);
        $this->assertEquals('September 3, 2020', $vi['primary_title']['release_date_displayable']);
        $this->assertEquals(2020, $vi['primary_title']['year']);
        $this->assertIsArray($vi['primary_title']['image']);

        // playback urls
        $this->assertCount(5, $vi['urls']);

        $this->assertEquals('AUTO', $vi['urls'][0]['quality']);
        $this->assertEquals('M3U8', $vi['urls'][0]['mime_type']);
        $this->assertNotNull($vi['urls'][0]['url']);

        $this->assertEquals('1080p', $vi['urls'][1]['quality']);
        $this->assertEquals('MP4', $vi['urls'][1]['mime_type']);
        $this->assertNotNull($vi['urls'][1]['url']);
    }

}
