<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Trailers extends Base
{
    /**
     * Get the latest trailers as seen on IMDb https://www.imdb.com/trailers/
     *
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function recentVideos(int $limit = 100): array
    {
        $query = <<<GRAPHQL
query RecentVideo {
  recentVideos(
    limit: $limit
    queryFilter: {contentTypes: TRAILER}
  ) {
    videos {
      id
      name {
        value
      }
      runtime {
        value
      }
      contentType {
        displayName {
          value
        }
      }
      description {
        value
      }
      thumbnail {
        url
        width
        height
      }
      createdDate
      primaryTitle {
        id
        titleText {
          text
        }
        releaseDate {
          displayableProperty {
            value {
              plainText
            }
          }
        }
        primaryImage {
          url
          width
          height
        }
      }
    }
  } 
}
GRAPHQL;
        $data = $this->graphql->query($query, "RecentVideo");

        $items = [];
        if (!isset($data->recentVideos->videos) || !is_array($data->recentVideos->videos)) {
            return $items;
        }

        foreach ($data->recentVideos->videos as $edge) {
            if (empty($edge->id) or empty($edge->primaryTitle->id)) {
                continue;
            }

            $items[] = [
                'id' => $edge->id,
                'playback_url' => $this->makeUrl('video', $edge->id),
                'created_date' => $edge->createdDate ?? null,
                'runtime_formatted' => $this->secondsToTimeFormat($edge->runtime->value),
                'runtime_seconds' => $edge->runtime->value ?? null,
                'title' => $edge->name->value ?? null,
                'description' => $edge->description->value ?? null,
                'content_type' => $edge->contentType->displayName->value ?? null,
                'thumbnail' => $this->parseImage($edge->thumbnail),
                'primary_title' => [
                    'id' => $edge->primaryTitle->id,
                    'title' => $edge->primaryTitle->titleText->text ?? null,
                    'release_date' => $edge->primaryTitle->releaseDate->displayableProperty->value->plainText ?? null,
                    'image' => $this->parseImage($edge->primaryTitle->primaryImage)
                ],
            ];
        }

        return $items;
    }

    /**
     * Get trending trailers as seen on IMDb https://www.imdb.com/trailers/
     *
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function trendingVideos(int $limit = 250): array
    {
        $query = <<<GRAPHQL
query TrendingVideo {
  trendingTitles(limit: $limit) {
    titles {
      id
      titleText {
        text
      }
      releaseDate {
        displayableProperty {
          value {
            plainText
          }
        }
      }
      primaryImage {
        url
        width
        height
      }
      latestTrailer {
        id
        name {
          value
        }
        runtime {
          value
        }
        contentType {
          displayName {
            value
          }
        }
        description {
          value
        }
        thumbnail {
          url
          width
          height
        }
        createdDate
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "TrendingVideo");

        $items = [];
        if (!isset($data->trendingTitles->titles) || !is_array($data->trendingTitles->titles)) {
            return $items;
        }

        foreach ($data->trendingTitles->titles as $edge) {
            if (empty($edge->latestTrailer->id) or empty($edge->id)) {
                continue;
            }

            $items[] = [
                'id' => $edge->latestTrailer->id,
                'playback_url' => $this->makeUrl('video', $edge->latestTrailer->id),
                'created_date' => $edge->latestTrailer->createdDate ?? null,
                'runtime_formatted' => $this->secondsToTimeFormat($edge->latestTrailer->runtime->value),
                'runtime_seconds' => $edge->latestTrailer->runtime->value ?? null,
                'title' => $edge->latestTrailer->name->value ?? null,
                'description' => $edge->latestTrailer->description->value ?? null,
                'content_type' => $edge->latestTrailer->contentType->displayName->value ?? null,
                'thumbnail' => $this->parseImage($edge->latestTrailer->thumbnail),
                'primary_title' => [
                    'id' => $edge->id,
                    'title' => $edge->titleText->text ?? null,
                    'release_date' => $edge->releaseDate->displayableProperty->value->plainText ?? null,
                    'image' => $this->parseImage($edge->primaryImage)
                ],
            ];
        }

        return $items;
    }
}

