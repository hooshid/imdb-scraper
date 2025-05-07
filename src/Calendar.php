<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Calendar extends Base
{
    /**
     * Get upcoming movie releases
     *
     * @param array $params
     *  Example: [
     *      'type' => 'MOVIE',
     *      'region' => 'US',
     *      'disablePopularityFilter' => 'true',
     *      'startDateOverride' => 0,
     *      'endDateOverride' => 90
     *  ]
     * @return array
     * @throws Exception
     */
    public function comingSoon(array $params = []): array
    {
        // Define default values for the parameters
        $defaults = [
            'type' => 'MOVIE',
            'region' => 'US',
            'disablePopularityFilter' => 'true',
            'startDateOverride' => 0,
            'endDateOverride' => 90
        ];

        // Merge the defaults with the incoming parameters
        $params = array_merge($defaults, $params);

        // Extract the parameters
        $type = strtoupper($params['type']);
        $region = strtoupper($params['region']);
        $disablePopularityFilter = $params['disablePopularityFilter'];
        $startDateOverride = $params['startDateOverride'];
        $endDateOverride = $params['endDateOverride'];

        $startDate = date('Y-m-d', strtotime($startDateOverride . ' day', time()));
        $futureDate = date('Y-m-d', strtotime($endDateOverride . ' day', time()));

        $types = ['MOVIE', 'TV', 'TV_EPISODE'];
        if (!in_array($type, $types)) {
            return [];
        }

        $query = <<<GRAPHQL
query ComingSoon {
    comingSoon(
      first: 9999
      comingSoonType: $type
      disablePopularityFilter: $disablePopularityFilter
      regionOverride: "$region"
      releasingOnOrAfter: "$startDate"
      releasingOnOrBefore: "$futureDate"
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

        $list = [];
        if (!isset($data->comingSoon->edges) || !is_array($data->comingSoon->edges)) {
            return $list;
        }

        foreach ($data->comingSoon->edges as $edge) {
            if (empty($edge->node->id) or empty($edge->node->titleText->text)) {
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
                    $genres[] = $genre->genre->text;
                }
            }

            // Cast
            $cast = [];
            if (isset($edge->node->principalCredits[0])) {
                foreach ($edge->node->principalCredits[0]->credits as $credit) {
                    $cast[] = $credit->name->nameText->text;
                }
            }

            $list[] = [
                "id" => $edge->node->id,
                "title" => $edge->node->titleText->text,
                "release_date" => $releaseDate,
                "genres" => $genres,
                "cast" => $cast,
                "image" => $this->image($edge->node->primaryImage)
            ];
        }

        return $list;
    }
}
