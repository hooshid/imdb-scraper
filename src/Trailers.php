<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Trailers extends Base
{
    /**
     * Get the latest trailers as seen on IMDb https://www.imdb.com/trailers/
     *
     * @param int $limit Maximum number of trailers to retrieve (default: 100)
     * @return array<int, array{
     *     id: string,
     *     playback_url: string,
     *     created_date: string|null,
     *     is_mature: bool|null,
     *     runtime_formatted: string|null,
     *     runtime_seconds: int|null,
     *     video_aspect_ratio: float|null,
     *     title: string|null,
     *     description: string|null,
     *     content_type: string|null,
     *     thumbnail: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null,
     *     primary_title: array{
     *         id: string,
     *         title: string|null,
     *         release_date: string|null,
     *         release_date_displayable: string|null,
     *         year: int|null,
     *         image: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }|null
     *     }
     * }> Returns list of recent trailers with:
     *     - 'id': Video ID
     *     - 'playback_url': Full URL to watch the trailer
     *     - 'created_date': Formatted creation date (YYYY-MM-DD HH:MM:SS)
     *     - 'is_mature': True or False for maturity
     *     - 'runtime_formatted': Human-readable runtime (MM:SS)
     *     - 'runtime_seconds': Runtime in seconds
     *     - 'video_aspect_ratio': Video aspect ratio
     *     - 'title': Trailer title
     *     - 'description': Trailer description
     *     - 'content_type': Type of video content (e.g. "Trailer")
     *     - 'thumbnail': Thumbnail image with dimensions
     *     - 'primary_title': Associated movie/TV show information with:
     *         - 'id': IMDb title ID
     *         - 'title': Title name
     *         - 'release_date': Formatted release date (YYYY-MM-DD) (e.g. "2025-08-01")
     *         - 'release_date_displayable': Formatted release date (F j, YYYY) (e.g. "August 1, 2025")
     *         - 'year': Release year (YYYY) (e.g. "2025")
     *         - 'image': Primary title image with dimensions
     * @throws Exception If API request fails
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
      videoDimensions {
        aspectRatio
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
      isMature
      primaryTitle {
        id
        titleText {
          text
        }
        releaseDate {
          day
          month
          year
          displayableProperty {
            value {
              plainText
            }
          }
        }
        releaseYear {
          year
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

        if (!isset($data->recentVideos->videos) || !is_array($data->recentVideos->videos) || count($data->recentVideos->videos) === 0) {
            return [];
        }

        $videoClass = new Video();
        return $videoClass->parseVideoResults($data->recentVideos->videos);
    }

    /**
     * Get trending trailers as seen on IMDb https://www.imdb.com/trailers/
     *
     * @param int $limit Maximum number of trending trailers to retrieve (default: 250)
     * @return array<int, array{
     *     id: string,
     *     playback_url: string,
     *     created_date: string|null,
     *     is_mature: bool|null,
     *     runtime_formatted: string|null,
     *     runtime_seconds: int|null,
     *     video_aspect_ratio: float|null,
     *     title: string|null,
     *     description: string|null,
     *     content_type: string|null,
     *     thumbnail: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null,
     *     primary_title: array{
     *         id: string,
     *         title: string|null,
     *         release_date: string|null,
     *         release_date_displayable: string|null,
     *         year: int|null,
     *         image: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }|null
     *     }
     * }> Returns list of trending trailers with:
     *     - 'id': Video ID
     *     - 'playback_url': Full URL to watch the trailer
     *     - 'created_date': Formatted creation date (YYYY-MM-DD HH:MM:SS)
     *     - 'is_mature': True or False for maturity
     *     - 'runtime_formatted': Human-readable runtime (MM:SS)
     *     - 'runtime_seconds': Runtime in seconds
     *     - 'video_aspect_ratio': Video aspect ratio
     *     - 'title': Trailer title
     *     - 'description': Detailed trailer description
     *     - 'content_type': Type of video content (e.g. "Trailer")
     *     - 'thumbnail': Thumbnail image with dimensions
     *     - 'primary_title': Associated movie/TV show information with:
     *         - 'id': IMDb title ID
     *         - 'title': Title name
     *         - 'release_date': Formatted release date (YYYY-MM-DD) (e.g. "2025-08-01")
     *         - 'release_date_displayable': Formatted release date (F j, YYYY) (e.g. "August 1, 2025")
     *         - 'year': Release year (YYYY) (e.g. "2025")
     *         - 'image': Primary title image with dimensions
     * @throws Exception If API request fails
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
        day
        month
        year
        displayableProperty {
          value {
            plainText
          }
        }
      }
      releaseYear {
        year
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
        videoDimensions {
          aspectRatio
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
        isMature
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "TrendingVideo");

        if (!isset($data->trendingTitles->titles) || !is_array($data->trendingTitles->titles) || count($data->trendingTitles->titles) === 0) {
            return [];
        }

        $items = [];

        foreach ($data->trendingTitles->titles as $edge) {
            if (empty($edge->latestTrailer->id) || empty($edge->id)) {
                continue;
            }

            $releaseDate = $this->buildDate(
                $edge->releaseDate->day ?? null,
                $edge->releaseDate->month ?? null,
                $edge->releaseDate->year ?? null
            );

            $items[] = [
                'id' => $edge->latestTrailer->id,
                'playback_url' => $this->makeUrl('video', $edge->latestTrailer->id),
                'created_date' => $edge->latestTrailer->createdDate ? $this->reformatDate($edge->latestTrailer->createdDate) : null,
                'is_mature' => $edge->latestTrailer->isMature ?? null,
                'runtime_formatted' => $this->secondsToTimeFormat($edge->latestTrailer->runtime->value),
                'runtime_seconds' => $edge->latestTrailer->runtime->value ?? null,
                'video_aspect_ratio' => $edge->latestTrailer->videoDimensions->aspectRatio ?? null,
                'title' => $edge->latestTrailer->name->value ?? null,
                'description' => $edge->latestTrailer->description->value ?? null,
                'content_type' => $edge->latestTrailer->contentType->displayName->value ?? null,
                'thumbnail' => $this->parseImage($edge->latestTrailer->thumbnail),
                'primary_title' => [
                    'id' => $edge->id,
                    'title' => $edge->titleText->text ?? null,
                    'release_date' => $releaseDate,
                    'release_date_displayable' => $edge->releaseDate->displayableProperty->value->plainText ?? null,
                    'year' => $edge->releaseYear->year ?? null,
                    'image' => $this->parseImage($edge->primaryImage ?? null)
                ],
            ];
        }

        return $items;
    }
}

