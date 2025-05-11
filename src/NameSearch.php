<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use stdClass;

class NameSearch extends Base
{
    /**
     * Search for names on IMDb
     *
     * @param array $params
     *  Example: [
     *      'name' => '',
     *      'birthMonthDay' => '',
     *      'birthDateRangeStart' => '',
     *      'birthDateRangeEnd' => '',
     *      'deathDateRangeStart' => '',
     *      'deathDateRangeEnd' => '',
     *      'birthPlace' => '',
     *      'gender' => '',
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
        $data = $this->graphql->query($query, "AdvancedNameSearch");

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
     * @param array $params
     * @return string
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
     * Generic date constraint builder
     *
     * @param string $type (birthDate, deathDate)
     * @param string|null $startDate (searches between startDate and present date) iso date string ('1975-01-01')
     * @param string|null $endDate (searches between endDate and earlier) iso date string ('1975-01-01')
     * @return string|null
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
     * Build the GraphQL query
     *
     * @param array $params
     * @param string $constraints
     * @return string
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
     * Process GraphQL response into standardized results
     *
     * @param stdClass $data
     * @return array
     */
    private function processSearchResults(stdClass $data): array
    {
        if (!isset($data->advancedNameSearch->edges) || !is_array($data->advancedNameSearch->edges)) {
            return [];
        }

        $results = [];

        $index = 1;
        foreach ($data->advancedNameSearch->edges as $edge) {
            $id = $edge->node->name->id ?? null;
            $name = $edge->node->name->nameText->text ?? null;

            if (empty($id) or empty($name)) {
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
                    $knownFor[] = [
                        'id' => $known->node->credit->title->id ?? null,
                        'title' => $known->node->credit->title->titleText->text ?? null,
                        'year' => $known->node->credit->title->releaseYear->year ?? null,
                        'end_year' => $known->node->credit->title->releaseYear->endYear ?? null
                    ];
                }
            }

            $results[] = [
                'index' => $index,
                'id' => $id,
                'url' => $this->getBaseUrl() . "/name/" . $id,
                'name' => $this->cleanString($name),
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
