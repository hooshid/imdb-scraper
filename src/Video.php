<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Old\Base;
use Hooshid\ImdbScraper\Base\Config;

class Video extends Base
{
    protected array $data = [
        'result' => [],
    ];

    /**
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(Config $config = null)
    {
        parent::__construct($config);
    }

    /**
     * Search name
     *
     * @param string $id
     * @return array
     */
    public function video(string $id): array
    {
        // if id is empty or null
        if (empty($id)) {
            return [];
        }

        // if result exist
        if ($this->data['result']) {
            return $this->data['result'];
        }

        $dom = $this->getHtmlDomParser("/video/" . $id . "/");

        $list = $dom->find('#__NEXT_DATA__', 0);
        $jsonLD = json_decode($list->innerText());
        //print_r($jsonLD->props->pageProps->videoPlaybackData);

        $urls = [];
        if ($jsonLD->props->pageProps->videoPlaybackData->video->playbackURLs) {
            foreach ($jsonLD->props->pageProps->videoPlaybackData->video->playbackURLs as $url) {
                $urls[] = [
                    'quality' => $url->displayName->value,
                    'mime_type' => $url->videoMimeType,
                    'url' => $url->url,
                ];
            }
        }

        $this->data['result'] = [
            'id' => $jsonLD->props->pageProps->videoPlaybackData->video->id,
            'type' => $jsonLD->props->pageProps->videoPlaybackData->video->contentType->displayName->value,
            'title_id' => $jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->id,
            'title' => @$jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->titleText->text,
            'video_title' => $jsonLD->props->pageProps->videoPlaybackData->video->name->value,
            'description' => @$jsonLD->props->pageProps->videoPlaybackData->video->description->value,
            'caption' => @$jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->primaryImage->caption->plainText,
            'thumbnail' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->url,
            'thumbnail_width' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->width,
            'thumbnail_height' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->height,
            'aspect_ratio' => @$jsonLD->props->pageProps->videoPlaybackData->video->videoDimensions->aspectRatio,
            'runtime' => $this->secondsToTimeFormat($jsonLD->props->pageProps->videoPlaybackData->video->runtime->value),
            'runtime_sec' => $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value,
            'created_date' => $jsonLD->props->pageProps->videoPlaybackData->video->createdDate,
            'urls' => $urls,
        ];

        return $this->data['result'];
    }

    private function secondsToTimeFormat($seconds): string
    {
        // Calculate hours, minutes, and seconds
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        // Determine the format based on whether there are hours
        if ($hours > 0) {
            return sprintf("%d:%d:%02d", $hours, $minutes, $seconds);
        } else {
            return sprintf("%d:%02d", $minutes, $seconds);
        }
    }
}

