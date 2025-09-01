<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Video extends Base
{

    /**
     * Get video info by id with direct video
     *
     * @param string $id
     * @return array<{
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
     *     },
     *     urls: array{
     *         quality: string,
     *         mime_type: int,
     *         url: int
     *     }|null
     * }> Returns list of videos with:
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
     *     - 'urls': MP4 and M3U8 urls of video for play directly
     * @throws Exception
     */
    public function video(string $id): array
    {
        // if id is empty or null
        if (empty($id)) {
            return [];
        }

        $query = <<<GRAPHQL
query Video(\$id: ID!) {
  video(id: \$id) {
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
    videoDimensions {
      aspectRatio
    }
    playbackURLs {
      displayName {
        value
      }
      url
      videoMimeType
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
GRAPHQL;
        $data = $this->graphql->query($query, "Video", ["id" => $id]);

        if (empty($data->video)) {
            return [];
        }

        $video = $this->parseNode($data->video);

        if (isset($data->video->playbackURLs) &&
            is_array($data->video->playbackURLs) &&
            count($data->video->playbackURLs) > 0) {
            $urls = [];
            foreach ($data->video->playbackURLs as $url) {
                $urls[] = [
                    'quality' => $url->displayName->value,
                    'mime_type' => $url->videoMimeType,
                    'url' => $url->url,
                ];
            }
            $video['urls'] = $urls;
        } else {
            $video['urls'] = null;
        }

        return $video;
    }

    private function parseNode($node): array
    {
        $releaseDate = $this->buildDate(
            $node->primaryTitle->releaseDate->day ?? null,
            $node->primaryTitle->releaseDate->month ?? null,
            $node->primaryTitle->releaseDate->year ?? null
        );

        return [
            'id' => $node->id,
            'playback_url' => $this->makeUrl('video', $node->id),
            'created_date' => $node->createdDate ? $this->reformatDate($node->createdDate) : null,
            'is_mature' => $node->isMature ?? null,
            'runtime_formatted' => $this->secondsToTimeFormat($node->runtime->value),
            'runtime_seconds' => $node->runtime->value ?? null,
            'video_aspect_ratio' => $node->videoDimensions->aspectRatio ?? null,
            'title' => $node->name->value,
            'description' => $node->description->value ?? null,
            'content_type' => $node->contentType->displayName->value,
            'thumbnail' => $this->parseImage($node->thumbnail),
            'primary_title' => [
                'id' => $node->primaryTitle->id,
                'title' => $node->primaryTitle->titleText->text ?? null,
                'release_date' => $releaseDate,
                'release_date_displayable' => $node->primaryTitle->releaseDate->displayableProperty->value->plainText ?? null,
                'year' => $node->primaryTitle->releaseYear->year ?? null,
                'image' => $this->parseImage($node->primaryTitle->primaryImage ?? null)
            ],
        ];
    }

    /**
     * Process videos results from GraphQL response
     *
     * @param array<object> $edges
     * @param string|null $videoContentType
     * @param bool|null $videoIncludeMature
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
     * }> Returns list of videos with:
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
     */
    public function parseVideoResults(array $edges, string $videoContentType = null, bool $videoIncludeMature = null): array
    {
        $results = [];

        foreach ($edges as $edge) {
            $node = $edge->node ?? null;
            if (empty($node)) {
                $node = $edge ?? null;
            }

            if (empty($node->id) || empty($node->name->value)) {
                continue;
            }

            if (!empty($videoContentType) &&
                isset($node->contentType->displayName->value) &&
                $node->contentType->displayName->value !== $videoContentType
            ) {
                continue;
            }

            if (isset($node->isMature) && $videoIncludeMature === false && $node->isMature === true) {
                continue;
            }

            if(empty($node->primaryTitle->id)){
                continue;
            }

            $results[] = $this->parseNode($node);
        }

        return $results;
    }
}

