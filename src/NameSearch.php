<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use stdClass;

class NameSearch extends Base
{
    /**
     * Search for names on IMDb with advanced filtering
     *
     * @param array{
     *     name?: string,
     *     birthMonthDay?: string,
     *     birthDateRangeStart?: string,
     *     birthDateRangeEnd?: string,
     *     deathDateRangeStart?: string,
     *     deathDateRangeEnd?: string,
     *     birthPlace?: string,
     *     gender?: string,
     *     adult?: string,
     *     limit?: int,
     *     sortBy?: string,
     *     sortOrder?: string
     * } $params Search parameters:
     *     - name: Full or partial name to search
     *     - birthMonthDay: Month and day in MM-DD format (e.g. "04-02")
     *     - birthDateRangeStart: Birth date range start (YYYY-MM-DD)
     *     - birthDateRangeEnd: Birth date range end (YYYY-MM-DD)
     *     - deathDateRangeStart: Death date range start (YYYY-MM-DD)
     *     - deathDateRangeEnd: Death date range end (YYYY-MM-DD)
     *     - birthPlace: Birth place location
     *     - gender: Gender filter (MALE/FEMALE/NON_BINARY)
     *     - adult: Adult content filter (INCLUDE_ADULT/EXCLUDE_ADULT)
     *     - limit: Maximum results (default: 50)
     *     - sortBy: Sort field (POPULARITY/NAME/BIRTH_DATE)
     *     - sortOrder: Sort direction (ASC/DESC)
     * @return array{
     *     results: array<int, array{
     *         index: int,
     *         id: string,
     *         url: string,
     *         name: string,
     *         image: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }|null,
     *         professions: string[],
     *         bio: string|null,
     *         known_for: array<int, array{
     *             id: string,
     *             title: string,
     *             year: int|null,
     *             end_year: int|null
     *         }>
     *     }>,
     *     total: int
     * } Returns search results with:
     *     - results: Array of matched names containing:
     *         - index: Result position
     *         - id: IMDb name ID
     *         - url: Full IMDb URL
     *         - name: Full name
     *         - image: Primary image with dimensions
     *         - professions: Array of primary professions
     *         - bio: Biography text
     *         - known_for: Notable works with title IDs and years
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
        $data = $this->graphql->query($query, "AdvancedNameSearch");

        return $this->processSearchResults($data);
    }

    /**
     * Normalize and validate search parameters
     *
     * @param array<string, mixed> $params
     * @return array{
     *     name: string,
     *     birthMonthDay: string,
     *     birthDateRangeStart: string,
     *     birthDateRangeEnd: string,
     *     deathDateRangeStart: string,
     *     deathDateRangeEnd: string,
     *     birthPlace: string,
     *     gender: string,
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
            'name' => '',
            'birthMonthDay' => '',
            'birthDateRangeStart' => '',
            'birthDateRangeEnd' => '',
            'deathDateRangeStart' => '',
            'deathDateRangeEnd' => '',
            'birthPlace' => '',
            'gender' => '',
            'adult' => 'EXCLUDE_ADULT',
            'limit' => 50,
            'sortBy' => 'POPULARITY',
            'sortOrder' => 'ASC'
        ];

        // Merge the defaults with the incoming parameters
        $normalized = array_merge($defaults, $params);

        // Extract the parameters
        return [
            'name' => trim($normalized['name']),
            'birthMonthDay' => $normalized['birthMonthDay'],
            'birthDateRangeStart' => $normalized['birthDateRangeStart'],
            'birthDateRangeEnd' => $normalized['birthDateRangeEnd'],
            'deathDateRangeStart' => $normalized['deathDateRangeStart'],
            'deathDateRangeEnd' => $normalized['deathDateRangeEnd'],
            'birthPlace' => trim($normalized['birthPlace']),
            'gender' => strtoupper($normalized['gender']),
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
     *     name: string,
     *     birthMonthDay: string,
     *     birthDateRangeStart: string,
     *     birthDateRangeEnd: string,
     *     deathDateRangeStart: string,
     *     deathDateRangeEnd: string,
     *     birthPlace: string,
     *     gender: string,
     *     adult: string
     * } $params
     * @return string GraphQL's constraints string
     */
    private function buildConstraints(array $params): string
    {
        $constraints = [];

        // Search Name
        if ($params['name']) {
            $constraints[] = sprintf('nameTextConstraint: {searchTerm: "%s"}', addslashes($params['name']));
        }

        // Birth Day Month
        if ($params['birthMonthDay']) {
            $constraints[] = sprintf('birthDateConstraint: {birthday: "--%s"}', $params['birthMonthDay']);
        }

        // Birth Date Range
        if ($birthDateConstraint = $this->buildDateConstraint('birthDate', $params['birthDateRangeStart'], $params['birthDateRangeEnd'])) {
            $constraints[] = $birthDateConstraint;
        }

        // Death Date Range
        if ($deathDateConstraint = $this->buildDateConstraint('deathDate', $params['deathDateRangeStart'], $params['deathDateRangeEnd'])) {
            $constraints[] = $deathDateConstraint;
        }

        // Birthplace
        if ($params['birthPlace']) {
            $constraints[] = sprintf('birthPlaceConstraint: {birthPlace: "%s"}', addslashes($params['birthPlace']));
        }

        // Gender
        if ($params['gender']) {
            $constraints[] = sprintf('genderIdentityConstraint: {anyGender: ["%s"]}', $params['gender']);
        }

        if (empty($constraints)) {
            return '';
        }

        // Adult filter
        $constraints[] = sprintf('explicitContentConstraint: {explicitContentFilter: %s}', $params['adult']);

        return implode(' ', $constraints);
    }

    /**
     * Build date range constraint for GraphQL
     *
     * @param 'birthDate'|'deathDate' $type Date type to constrain
     * @param string|null $startDate Start date (YYYY-MM-DD)
     * @param string|null $endDate End date (YYYY-MM-DD)
     * @return string|null Formatted GraphQL constraint or null if invalid
     */
    private function buildDateConstraint(string $type, ?string $startDate, ?string $endDate): ?string
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

        return sprintf('%sConstraint: {%sRange: {%s}}', $type, $type, implode(', ', $range));
    }

    /**
     * Build the GraphQL query string
     *
     * @param array{
     *     limit: int,
     *     sortBy: string,
     *     sortOrder: string
     * } $params
     * @param string $constraints GraphQL's constraints string
     * @return string Complete GraphQL query
     */
    private function buildGraphQLQuery(array $params, string $constraints): string
    {
        return <<<GRAPHQL
query AdvancedNameSearch {
  advancedNameSearch(
    first: {$params['limit']},
    sort: {sortBy: {$params['sortBy']} sortOrder: {$params['sortOrder']}}
    constraints: {
        $constraints
    }
  ) {
    total
    edges {
      node {
        name {
          id
          nameText {
            text
          }
          bio {
            text {
              plainText
            }
          }
          primaryImage {
            url
            width
            height
          }
          primaryProfessions {
            category {
              text
            }
          }
          knownFor(first: 5) {
            edges {
              node {
                credit {
                  title {
                    id
                    titleText {
                      text
                    }
                    releaseYear {
                      year
                      endYear
                    }
                  }
                }
              }
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
        if (!$this->hasArrayItems($data->advancedNameSearch->edges)) {
            return [
                'results' => [],
                'total' => 0
            ];
        }

        $results = [];
        $index = 1;

        foreach ($data->advancedNameSearch->edges as $edge) {
            $id = $edge->node->name->id ?? null;
            $name = $edge->node->name->nameText->text ?? null;

            if (empty($id) || empty($name)) {
                continue;
            }

            // Professions
            $professions = [];
            if (isset($edge->node->name->primaryProfessions)) {
                foreach ($edge->node->name->primaryProfessions as $profession) {
                    $professions[] = $profession->category->text;
                }
            }

            // Bio
            $bio = null;
            if (isset($edge->node->name->bio->text->plainText)) {
                $bio = nl2br($edge->node->name->bio->text->plainText);
            }

            // knownFor
            $knownFor = [];
            if (!empty($edge->node->name->knownFor->edges)) {
                foreach ($edge->node->name->knownFor->edges as $known) {
                    if (empty($known->node->credit->title->id) || empty($known->node->credit->title->titleText->text)) {
                        continue;
                    }
                    $knownFor[] = [
                        'id' => $known->node->credit->title->id,
                        'title' => $known->node->credit->title->titleText->text,
                        'year' => $known->node->credit->title->releaseYear->year ?? null,
                        'end_year' => $known->node->credit->title->releaseYear->endYear ?? null
                    ];
                }
            }

            $results[] = [
                'index' => $index,
                'id' => $id,
                'url' => $this->makeUrl("name", $id),
                'name' => $name,
                'image' => $this->parseImage($edge->node->name->primaryImage),
                'professions' => $professions,
                'bio' => $bio,
                'known_for' => $knownFor,
            ];
            $index++;
        }

        return [
            'results' => $results,
            'total' => $data->advancedNameSearch->total
        ];
    }
}
