<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;

class Video extends Base
{

    /**
     * Process videos results from GraphQL response
     *
     * @param array<object> $edges
     * @return array<int, array{
     *     id: string,
     *     playback_url: string,
     *     created_date: string|null,
     *     runtime_formatted: string|null,
     *     runtime_seconds: int|null,
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
     *     - 'runtime_formatted': Human-readable runtime (MM:SS)
     *     - 'runtime_seconds': Runtime in seconds
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
    public function parseVideoResults(array $edges): array
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

            $releaseDate = $this->buildDate(
                $edge->primaryTitle->releaseDate->day ?? null,
                $edge->primaryTitle->releaseDate->month ?? null,
                $edge->primaryTitle->releaseDate->year ?? null
            );

            $results[] = [
                'id' => $node->id,
                'playback_url' => $this->makeUrl('video', $node->id),
                'created_date' => $node->createdDate ? $this->reformatDate($node->createdDate) : null,
                'runtime_formatted' => $this->secondsToTimeFormat($node->runtime->value),
                'runtime_seconds' => $node->runtime->value ?? null,
                'title' => $node->name->value,
                'description' => $node->description->value ?? null,
                'content_type' => $node->contentType->displayName->value,
                'thumbnail' => $this->parseImage($node->thumbnail),
                'primary_title' => [
                    'id' => $node->primaryTitle->id,
                    'title' => $node->primaryTitle->titleText->text ?? null,
                    'release_date' => $releaseDate,
                    'release_date_displayable' => $edge->primaryTitle->releaseDate->displayableProperty->value->plainText ?? null,
                    'year' => $node->primaryTitle->releaseYear->year ?? null,
                    'image' => $this->parseImage($node->primaryTitle->primaryImage ?? null)
                ],
            ];
        }

        return $results;
    }
}

