<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Calendar extends Base
{
    /**
     * Get upcoming movie/TV releases
     *
     * @param array{
     *     type?: string,
     *     region?: string,
     *     disablePopularityFilter?: string,
     *     startDateOverride?: int,
     *     endDateOverride?: int
     * } $params Optional parameters:
     *     - type: Content type (MOVIE, TV, TV_EPISODE) - default: MOVIE
     *     - region: Region code (e.g. US) - default: US
     *     - disablePopularityFilter: Whether to disable popularity filter (true/false) - default: true
     *     - startDateOverride: Days from today for start date - default: 0 (today)
     *     - endDateOverride: Days from today for end date - default: 90
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     release_date: string,
     *     genres: string[],
     *     cast: string[],
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }> Returns list of upcoming releases with:
     *     - 'id': IMDb title ID
     *     - 'title': Title name
     *     - 'release_date': Date in YYYY-MM-DD format
     *     - 'genres': Array of genre names
     *     - 'cast': Array of principal cast members
     *     - 'image': Primary image with dimensions
     * @throws Exception If API request fails
     */
    public function comingSoon(array $params = []): array
    {
        $params = $this->normalizeParameters($params);

        $startDate = date('Y-m-d', strtotime($params['startDateOverride'] . ' day'));
        $endDate = date('Y-m-d', strtotime($params['endDateOverride'] . ' day'));

        $types = ['MOVIE', 'TV', 'TV_EPISODE'];
        if (!in_array($params['type'], $types)) {
            return [];
        }

        $query = <<<GRAPHQL
query ComingSoon {
    comingSoon(
      first: 9999
      comingSoonType: {$params['type']}
      disablePopularityFilter: {$params['disablePopularityFilter']}
      regionOverride: "{$params['region']}"
      releasingOnOrAfter: "$startDate"
      releasingOnOrBefore: "$endDate"
      sort: {sortBy: RELEASE_DATE, sortOrder: ASC}) {
    edges {
      node {
        id
        titleText {
          text
        }
        releaseDate {
          day
          month
          year
        }
        titleGenres {
          genres {
            genre {
              text
            }
          }
        }
        principalCredits(filter: {categories: "cast"}) {
          credits {
            name {
              nameText {
                text
              }
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
        $data = $this->graphql->query($query, "ComingSoon");

        if (!isset($data->comingSoon->edges) || !is_array($data->comingSoon->edges) || count($data->comingSoon->edges) === 0) {
            return [];
        }

        $list = [];

        foreach ($data->comingSoon->edges as $edge) {
            if (empty($edge->node->id) || empty($edge->node->titleText->text)) {
                continue;
            }

            // Release date
            $releaseDate = $this->buildDate($edge->node->releaseDate->day, $edge->node->releaseDate->month, $edge->node->releaseDate->year);
            if (empty($releaseDate)) {
                continue;
            }

            // Genres
            $genres = [];
            if (isset($edge->node->titleGenres)) {
                foreach ($edge->node->titleGenres->genres as $genre) {
                    if (!empty($genre->genre->text)) {
                        $genres[] = $genre->genre->text;
                    }
                }
            }

            // Cast
            $cast = [];
            if (isset($edge->node->principalCredits[0])) {
                foreach ($edge->node->principalCredits[0]->credits as $credit) {
                    if (!empty($credit->name->nameText->text)) {
                        $cast[] = $credit->name->nameText->text;
                    }
                }
            }

            $list[] = [
                "id" => $edge->node->id,
                "title" => $edge->node->titleText->text,
                "release_date" => $releaseDate,
                "genres" => $genres,
                "cast" => $cast,
                "image" => $this->parseImage($edge->node->primaryImage ?? null)
            ];
        }

        return $list;
    }

    /**
     * Normalize and validate parameters
     *
     * @param array $params
     * @return array{
     *     type: string,
     *     region: string,
     *     disablePopularityFilter: string,
     *     startDateOverride: int,
     *     endDateOverride: int
     * }
     */
    private function normalizeParameters(array $params): array
    {
        $defaults = [
            'type' => 'MOVIE',
            'region' => 'US',
            'disablePopularityFilter' => 'true',
            'startDateOverride' => 0,
            'endDateOverride' => 90
        ];

        $merged = array_merge($defaults, $params);

        return [
            'type' => strtoupper($merged['type']),
            'region' => strtoupper($merged['region']),
            'disablePopularityFilter' => $merged['disablePopularityFilter'],
            'startDateOverride' => (int)$merged['startDateOverride'],
            'endDateOverride' => (int)$merged['endDateOverride']
        ];
    }
}
