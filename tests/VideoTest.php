<?php

use Hooshid\ImdbScraper\Base\Config;
use Hooshid\ImdbScraper\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    protected function getVideo($language = "en-US"): Video
    {
        $config = new Config();
        $config->language = $language;

        return new Video($config);
    }

    public function testVideo1()
    {
        $video = $this->getVideo();
        $vi = $video->video('vi3067265305'); // Tenet - Official Trailer

        $this->assertIsArray($vi);
        $this->assertCount(15, $vi);
        $this->assertEquals('vi3067265305', $vi['id']);
        $this->assertEquals('Trailer', $vi['type']);
        $this->assertEquals('tt6723592', $vi['title_id']);
        $this->assertEquals('Tenet', $vi['title']);
        $this->assertEquals('Tenet', $vi['video_title']);
        $this->assertEquals('Official Trailer', $vi['description']);
        $this->assertEquals('John David Washington in Tenet (2020)', $vi['caption']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BZWEyNGRmMDUtM2E5MS00ZTM3LTk5ZWEtNjJmYTAzNTFiNzg3XkEyXkFqcGdeQVRoaXJkUGFydHlJbmdlc3Rpb25Xb3JrZmxvdw@@._V1_.jpg', $vi['thumbnail']);
        $this->assertEquals(1920, $vi['thumbnail_width']);
        $this->assertEquals(1080, $vi['thumbnail_height']);
        $this->assertEquals(1.777778, $vi['aspect_ratio']);
        $this->assertEquals('2:03', $vi['runtime']);
        $this->assertEquals(123, $vi['runtime_sec']);
        $this->assertEquals('2020-10-31T17:04:10.118Z', $vi['created_date']);

        // playback urls
        $this->assertCount(5, $vi['urls']);

        $this->assertEquals('AUTO', $vi['urls'][0]['quality']);
        $this->assertEquals('M3U8', $vi['urls'][0]['mime_type']);
        $this->assertNotNull($vi['urls'][0]['url']);

        $this->assertEquals('1080p', $vi['urls'][1]['quality']);
        $this->assertEquals('MP4', $vi['urls'][1]['mime_type']);
        $this->assertNotNull($vi['urls'][1]['url']);
    }

    public function testVideo2()
    {
        $video = $this->getVideo();
        $vi = $video->video('vi1574944793'); // Cruella - Meet the Villain

        $this->assertIsArray($vi);
        $this->assertCount(15, $vi);
        $this->assertEquals('vi1574944793', $vi['id']);
        $this->assertEquals('Trailer', $vi['type']);
        $this->assertEquals('tt3228774', $vi['title_id']);
        $this->assertEquals('Cruella', $vi['title']);
        $this->assertEquals('Meet the Villain', $vi['video_title']);
        $this->assertEquals('', $vi['description']);
        $this->assertEquals('Emma Stone in Cruella (2021)', $vi['caption']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BYzVlNmMzMTUtOGJmMi00YzU4LWI5Y2ItZTRkODQzM2I1MjA2XkEyXkFqcGdeQXRyYW5zY29kZS13b3JrZmxvdw@@._V1_.jpg', $vi['thumbnail']);
        $this->assertEquals(1920, $vi['thumbnail_width']);
        $this->assertEquals(1080, $vi['thumbnail_height']);
        $this->assertEquals(1.777778, $vi['aspect_ratio']);
        $this->assertEquals('0:46', $vi['runtime']);
        $this->assertEquals(46, $vi['runtime_sec']);
        $this->assertEquals('2021-05-12T09:17:17.791Z', $vi['created_date']);

        // playback urls
        $this->assertCount(5, $vi['urls']);
    }

}
