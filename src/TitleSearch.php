<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class TitleSearch extends Base
{
    /**
     * @param array $params
     *  Example: [
     *      'searchTerm' => '',
     *      'types' => '',
     *      'genres' => '',
     *      'creditId' => '',
     *      'startDate' => '',
     *      'endDate' => '',
     *      'countries' => '',
     *      'languages' => '',
     *      'companies' => '',
     *      'keywords' => '',
     *      'adult' => 'EXCLUDE_ADULT',
     *      'limit' => 50,
     *      'sortBy' => 'POPULARITY',
     *      'sortOrder' => 'ASC'
     *  ]
     * @return array
     * @throws Exception
     */
    public function search(array $params = []): array
    {
        $params = $this->normalizeParameters($params);
        $constraints = $this->buildConstraints($params);

        if (empty($constraints)) {
            return [];
        }

        $query = $this->buildGraphQLQuery($params, $constraints);
        $data = $this->graphql->query($query, "advancedSearch");

        return $this->processSearchResults($data);
    }

    /**
     * Normalize and validate search parameters
     *
     * @param array $params
     * @return array
     */
    private function normalizeParameters(array $params): array
    {
        // Define default values for the parameters
        $defaults = [
            'searchTerm' => '',
            'types' => '',
            'genres' => '',
            'creditId' => '',
            'startDate' => '',
            'endDate' => '',
            'countries' => '',
            'languages' => '',
            'companies' => '',
            'keywords' => '',
            'adult' => 'EXCLUDE_ADULT',
            'limit' => 50,
            'sortBy' => 'POPULARITY', // POPULARITY, RANKING, RELEASE_DATE, YEAR, RUNTIME, BOX_OFFICE_GROSS_DOMESTIC, METACRITIC_SCORE
            'sortOrder' => 'ASC' // ASC, DESC
        ];

        // Merge the defaults with the incoming parameters
        $normalized = array_merge($defaults, $params);

        // Extract the parameters
        return [
            'searchTerm' => trim($normalized['searchTerm']),
            'types' => trim($normalized['types']),
            'genres' => trim($normalized['genres']),
            'creditId' => trim($normalized['creditId']),
            'startDate' => trim($normalized['startDate']),
            'endDate' => trim($normalized['endDate']),
            'countries' => trim($normalized['countries']),
            'languages' => trim($normalized['languages']),
            'companies' => trim($normalized['companies']),
            'keywords' => trim($normalized['keywords']),
            'adult' => strtoupper($normalized['adult']),
            'limit' => (int)$normalized['limit'],
            'sortBy' => strtoupper($normalized['sortBy']),
            'sortOrder' => strtoupper($normalized['sortOrder'])
        ];
    }

    /**
     * Build GraphQL constraints from parameters
     *
     * @param array $params
     * @return string
     */
    private function buildConstraints(array $params): string
    {
        $constraints = [];

        if (!empty($params['searchTerm'])) {
            $constraints[] = sprintf('titleTextConstraint: {searchTerm: "%s"}', addslashes($params['searchTerm']));
        }

        if (!empty($params['genres'])) {
            $constraints[] = sprintf('genreConstraint: {allGenreIds: [%s]}', $this->formatList($params['genres']));
        }

        if (!empty($params['types'])) {
            $constraints[] = sprintf('titleTypeConstraint: {anyTitleTypeIds: [%s]}', $this->formatList($params['types']));
        }

        if (!empty($params['creditId'])) {
            $constraints[] = sprintf('creditedNameConstraint: {anyNameIds: [%s]}', $this->formatList($params['creditId']));
        }

        if ($releaseDates = $this->buildReleaseDateConstraint($params['startDate'], $params['endDate'])) {
            $constraints[] = $releaseDates;
        }

        if (!empty($params['countries'])) {
            $constraints[] = sprintf('originCountryConstraint: {anyCountries: [%s]}', $this->formatList($params['countries']));
        }

        if (!empty($params['languages'])) {
            $constraints[] = sprintf('languageConstraint: {anyLanguages: [%s]}', $this->formatList($params['languages']));
        }

        if (!empty($params['companies'])) {
            $constraints[] = sprintf('creditedCompanyConstraint: {anyCompanyIds: [%s]}', $this->formatList($params['companies']));
        }

        if (!empty($params['keywords'])) {
            $keywords = strtolower(str_replace(" ", "-", $params['keywords']));
            $constraints[] = sprintf('keywordConstraint: {anyKeywords: [%s]}', $this->formatList($keywords));
        }

        if (empty($constraints)) {
            return '';
        }

        // Adult filter
        $constraints[] = sprintf('explicitContentConstraint: {explicitContentFilter: %s}', $params['adult']);

        return implode(' ', $constraints);
    }

    /**
     * Format comma-separated list into quoted values
     *
     * @param string $items
     * @return string
     */
    private function formatList(string $items): string
    {
        if (empty($items)) {
            return '';
        }

        return '"' . implode('","', array_map('trim', explode(',', $items))) . '"';
    }

    /**
     * Build release date constraint
     *
     * @param string $startDate
     * @param string $endDate
     * @return string|null
     */
    private function buildReleaseDateConstraint(string $startDate, string $endDate): ?string
    {
        if (empty($startDate) && empty($endDate)) {
            return null;
        }

        $range = [];

        if (!empty($startDate) && $this->validateDate($startDate)) {
            $range[] = sprintf('start: "%s"', $startDate);
        }

        if (!empty($endDate) && $this->validateDate($endDate)) {
            $range[] = sprintf('end: "%s"', $endDate);
        }

        if (empty($range)) {
            return null;
        }

        return sprintf('releaseDateConstraint: {releaseDateRange: {%s}}', implode(', ', $range));
    }

    /**
     * Build the GraphQL query
     *
     * @param array $params
     * @param string $constraints
     * @return string
     */
    private function buildGraphQLQuery(array $params, string $constraints): string
    {
        return <<<GRAPHQL
query advancedSearch {
  advancedTitleSearch(
    first: {$params['limit']},
    sort: {sortBy: {$params['sortBy']}, sortOrder: {$params['sortOrder']}}
    constraints: {
      $constraints
    }
  ) {
    total
    edges {
      node {
        title {
          id
          originalTitleText {
            text
          }
          titleText {
            text
          }
          titleType {
            text
          }
          releaseYear {
            year
            endYear
          }
          primaryImage {
            url
            width
            height
          }
          runtime {
            seconds
          }
          ratingsSummary {
            aggregateRating
            voteCount
          }
          plot {
            plotText {
              plainText
            }
          }
          metacritic {
            metascore {
              score
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
    }

    /**
     * Process GraphQL response into standardized results
     */
    private function processSearchResults(\stdClass $data): array
    {
        if (!isset($data->advancedTitleSearch->edges) || !is_array($data->advancedTitleSearch->edges)) {
            return [];
        }

        $results = [];

        foreach ($data->advancedTitleSearch->edges as $edge) {
            if (empty($edge->node->title->id)) {
                continue;
            }

            $imdbId = $edge->node->title->id;
            $yearRange = null;
            if (isset($edge->node->title->releaseYear->year)) {
                $yearRange .= $edge->node->title->releaseYear->year;
                if (isset($edge->node->title->releaseYear->endYear)) {
                    $yearRange .= '-' . $edge->node->title->releaseYear->endYear;
                }
            }

            $results[] = [
                'id' => $imdbId,
                'url' => $this->getBaseUrl() . "/title/" . $imdbId,
                'originalTitle' => $edge->node->title->titleText->text ?? null,
                'title' => $edge->node->title->titleText->text ?? null,
                'type' => $edge->node->title->titleType->text ?? null,
                'year' => $yearRange,
                'plot' => $edge->node->title->plot->plotText->plainText ?? null,
                'runtime' => $this->secondsToMinutes($edge->node->title->runtime->seconds ?? null),
                'rating' => $edge->node->title->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->node->title->ratingsSummary->voteCount ?? null,
                'metacritic' => $edge->node->title->metacritic->metascore->score ?? null,
                'image' => $this->parseImage($edge->node->title->primaryImage)
            ];
        }

        return [
            'results' => $results,
            'total' => $data->advancedTitleSearch->total
        ];
    }
}

