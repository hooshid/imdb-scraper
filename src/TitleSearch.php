<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use stdClass;

class TitleSearch extends Base
{
    /**
     * Search for titles on IMDb with advanced filtering
     *
     * @param array{
     *     searchTerm?: string,
     *     types?: string,
     *     genres?: string,
     *     creditId?: string,
     *     startDate?: string,
     *     endDate?: string,
     *     countries?: string,
     *     languages?: string,
     *     companies?: string,
     *     keywords?: string,
     *     adult?: string,
     *     limit?: int,
     *     sortBy?: string,
     *     sortOrder?: string
     * } $params Search parameters:
     *     - searchTerm: Title to search for
     *     - types: Comma-separated title types (movie,tvSeries,etc)
     *     - genres: Comma-separated genres (Action,Adventure,etc)
     *     - creditId: Comma-separated name IDs to filter by
     *     - startDate/endDate: Release date range (YYYY-MM-DD)
     *     - countries: Comma-separated country codes
     *     - languages: Comma-separated language codes
     *     - companies: Comma-separated company IDs
     *     - keywords: Comma-separated keywords
     *     - adult: Content filter (EXCLUDE_ADULT/INCLUDE_ADULT)
     *     - limit: Results per page (default: 50)
     *     - sortBy: Field to sort by (POPULARITY,RELEASE_DATE,etc)
     *     - sortOrder: Sort direction (ASC/DESC)
     * @return array{
     *     results: array<int, array{
     *         id: string,
     *         url: string,
     *         title: string,
     *         originalTitle: string|null,
     *         type: string|null,
     *         year: int|null,
     *         end_year: int|null,
     *         plot: string|null,
     *         runtime_formatted: string|null,
     *         runtime_minutes: int|null,
     *         runtime_seconds: int|null,
     *         rating: float|null,
     *         votes: int|null,
     *         metacritic: int|null,
     *         image: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }|null
     *     }>,
     *     total: int
     * } Returns search results containing:
     *     - results: Array of matched titles with detailed info
     *     - total: Total number of matching results
     * @throws Exception If API request fails
     */
    public function search(array $params = []): array
    {
        $params = $this->normalizeParameters($params);
        $constraints = $this->buildConstraints($params);

        if (empty($constraints)) {
            return [
                'results' => [],
                'total' => 0
            ];
        }

        $query = $this->buildGraphQLQuery($params, $constraints);
        $data = $this->graphql->query($query, "advancedSearch");

        return $this->processSearchResults($data);
    }

    /**
     * Normalize and validate search parameters
     *
     * @param array<string, mixed> $params
     * @return array{
     *     searchTerm: string,
     *     types: string,
     *     genres: string,
     *     creditId: string,
     *     startDate: string,
     *     endDate: string,
     *     countries: string,
     *     languages: string,
     *     companies: string,
     *     keywords: string,
     *     adult: string,
     *     limit: int,
     *     sortBy: string,
     *     sortOrder: string
     * }
     */
    private function normalizeParameters(array $params): array
    {
        // Define default values for the parameters
        $defaults = [
            'searchTerm' => '',
            'types' => '', // movie, tvSeries, short, tvEpisode, tvMiniSeries, tvMovie, tvSpecial, tvShort, videoGame, video, musicVideo, podcastSeries, podcastEpisode
            'genres' => '', // Action, Adult, Adventure, Animation, Biography, Comedy, Crime, Documentary, Drama, Family, Fantasy, Film-Noir, Game-Show, History, Horror, Music, Musical, Mystery, News, Reality-TV,Romance, Sci-Fi, Short, Sport, Talk-Show, Thriller, War, Western
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
     * @param array{
     *     searchTerm: string,
     *     types: string,
     *     genres: string,
     *     creditId: string,
     *     startDate: string,
     *     endDate: string,
     *     countries: string,
     *     languages: string,
     *     companies: string,
     *     keywords: string,
     *     adult: string
     * } $params
     * @return string GraphQL's constraints string
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
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return string|null Formatted GraphQL constraint
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
     * Build the GraphQL query string
     *
     * @param array{
     *     limit: int,
     *     sortBy: string,
     *     sortOrder: string
     * } $params
     * @param string $constraints GraphQL constraints
     * @return string Complete GraphQL query
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
     * Process GraphQL response into standardized format
     *
     * @param stdClass $data Raw GraphQL response
     * @return array{
     *     results: array<int, array>,
     *     total: int
     * }
     */
    private function processSearchResults(stdClass $data): array
    {
        if (!$this->hasArrayItems($data->advancedTitleSearch->edges)) {
            return [
                'results' => [],
                'total' => 0
            ];
        }

        $results = [];

        foreach ($data->advancedTitleSearch->edges as $edge) {
            $title = $edge->node->title ?? null;
            if (empty($title->id) || empty($title->titleText->text)) {
                continue;
            }

            $results[] = [
                'id' => $title->id,
                'url' => $this->makeUrl("title", $title->id),
                'title' => $title->titleText->text,
                'original_title' => $title->originalTitleText->text ?? null,
                'type' => $title->titleType->text ?? null,
                'year' => $title->releaseYear->year ?? null,
                'end_year' => $title->releaseYear->endYear ?? null,
                'plot' => $title->plot->plotText->plainText ?? null,
                'runtime_formatted' => $this->secondsToTimeFormat($title->runtime->seconds ?? null),
                'runtime_minutes' => $this->secondsToMinutes($title->runtime->seconds ?? null),
                'runtime_seconds' => $title->runtime->seconds ?? null,
                'rating' => $title->ratingsSummary->aggregateRating ?? null,
                'votes' => $title->ratingsSummary->voteCount ?? null,
                'metacritic' => $title->metacritic->metascore->score ?? null,
                'image' => $this->parseImage($title->primaryImage)
            ];
        }

        return [
            'results' => $results,
            'total' => $data->advancedTitleSearch->total
        ];
    }
}

