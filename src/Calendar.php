<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Calendar extends Base
{
    /**
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

        $query = <<<EOF
query {
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
        titleText {
          text
        }
        id
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
        }
      }
    }
  }
}
EOF;
        $data = $this->graphql->query($query);

        $calendar = [];
        foreach ($data->comingSoon->edges as $edge) {
            $imdbId = $edge->node->id ?? '';
            $title = $edge->node->titleText->text ?? '';

            if (empty($imdbId) or empty($title)) {
                continue;
            }

            // Release date
            $releaseDate = [
                "day" => $edge->node->releaseDate->day ?? null,
                "month" => $edge->node->releaseDate->month ?? null,
                "year" => $edge->node->releaseDate->year ?? null
            ];

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

            // Image url
            $imageUrl = null;
            if (isset($edge->node->primaryImage->url) and !empty($edge->node->primaryImage->url)) {
                $imageUrl = $this->imageUrl($edge->node->primaryImage->url);
            }

            $calendar[] = [
                "id" => $imdbId,
                "title" => $title,
                "releaseDate" => $releaseDate,
                "genres" => $genres,
                "cast" => $cast,
                "imageUrl" => $imageUrl
            ];
        }

        return $calendar;
    }
}
