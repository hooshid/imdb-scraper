<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Video extends Base
{
    protected $data = [
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
     * @param $id
     * @return array
     */
    public function video($id): array
    {
        // if id is empty or null
        if (empty($id)) {
            return [];
        }

        if (substr($id, 0, 2) != 'vi') {
            $id = "vi" . $id;
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

        $runtime = null;
        if ($jsonLD->props->pageProps->videoPlaybackData->video->runtime->value <= 60) {
            $runtime = '0:' . date('s', $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value);
        } else if ($jsonLD->props->pageProps->videoPlaybackData->video->runtime->value <= 86400) {
            $runtime = ltrim(date('i:s', $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value), '0');
        } else if ($jsonLD->props->pageProps->videoPlaybackData->video->runtime->value > 86400) {
            $runtime = date('G:', $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value) . ltrim(date('i:s', $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value), '0');
        }

        $this->data['result'] = [
            'id' => $jsonLD->props->pageProps->videoPlaybackData->video->id,
            'type' => $jsonLD->props->pageProps->videoPlaybackData->video->contentType->displayName->value,
            'title_id' => $jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->id,
            'title' => $jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->titleText->text,
            'video_title' => $jsonLD->props->pageProps->videoPlaybackData->video->name->value,
            'description' => $jsonLD->props->pageProps->videoPlaybackData->video->description->value,
            'caption' => $jsonLD->props->pageProps->videoPlaybackData->video->primaryTitle->primaryImage->caption->plainText,
            'thumbnail' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->url,
            'thumbnail_width' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->width,
            'thumbnail_height' => $jsonLD->props->pageProps->videoPlaybackData->video->thumbnail->height,
            'aspect_ratio' => $jsonLD->props->pageProps->videoPlaybackData->video->videoDimensions->aspectRatio,
            'runtime' => $runtime,
            'runtime_sec' => $jsonLD->props->pageProps->videoPlaybackData->video->runtime->value,
            'created_date' => $jsonLD->props->pageProps->videoPlaybackData->video->createdDate,
            'urls' => $urls,
        ];

        return $this->data['result'];
    }
}

