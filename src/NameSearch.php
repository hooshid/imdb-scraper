<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class NameSearch extends Base
{
    /**
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
     */
    public function search(array $params = []): array
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
        $params = array_merge($defaults, $params);

        // Extract the parameters
        $searchTerm = trim($params['name']);
        $birthMonthDay = $params['birthMonthDay'];
        $birthDateRangeStart = $params['birthDateRangeStart'];
        $birthDateRangeEnd = $params['birthDateRangeEnd'];
        $deathDateRangeStart = $params['deathDateRangeStart'];
        $deathDateRangeEnd = $params['deathDateRangeEnd'];
        $birthPlace = trim($params['birthPlace']);
        $gender = strtoupper($params['gender']);
        $adult = strtoupper($params['adult']);

        $limit = (int)$params['limit'];
        $sortBy = strtoupper($params['sortBy']);
        $sortOrder = strtoupper($params['sortOrder']);

        $constraint = "";

        // Search Name
        if ($searchTerm) {
            $constraint .= "nameTextConstraint: {searchTerm: \"$searchTerm\"}";
        }

        // Birth Day Month
        if ($birthMonthDay) {
            $constraint .= "birthDateConstraint: {birthday: \"--$birthMonthDay\"}";
        }

        // Birth Date Range
        $birthDateRange = $this->checkBirthDates($birthDateRangeStart, $birthDateRangeEnd);
        if ($birthDateRange !== false) {
            $constraint .= $birthDateRange;
        }

        // Death Date Range
        $deathDateRange = $this->checkDeathDates($deathDateRangeStart, $deathDateRangeEnd);
        if ($deathDateRange !== false) {
            $constraint .= $deathDateRange;
        }

        // Birthplace
        if (!empty($birthPlace)) {
            $constraint .= ' birthPlaceConstraint: {birthPlace: "' . $birthPlace . '"}';
        }

        $query = <<<EOF
query AdvancedNameSearch {
  advancedNameSearch(
    first: $limit,
    sort: {sortBy: $sortBy sortOrder: $sortOrder}
    constraints: {
        $constraint
        genderIdentityConstraint: {anyGender: [$gender]}
        explicitContentConstraint: {explicitContentFilter: $adult}
    }
  ) {
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
          }
          primaryProfessions {
            category {
                text
            }
          }
        }
      }
    }
  }
}
EOF;

        $results = [];
        try {
            $data = $this->graphql->query($query, "AdvancedNameSearch");
        } catch (Exception $e) {
            return $results;
        }

        if (!isset($data->advancedNameSearch) ||
            empty($data->advancedNameSearch->edges) ||
            !is_array($data->advancedNameSearch->edges) ||
            count($data->advancedNameSearch->edges) == 0
        ) {
            return $results;
        }

        $index = 1;
        foreach ($data->advancedNameSearch->edges as $edge) {
            $imdbId = $edge->node->name->id ?? '';
            $name = $edge->node->name->nameText->text ?? '';

            if (empty($imdbId) or empty($name)) {
                continue;
            }

            // Image url
            $imageUrl = null;
            if (isset($edge->node->name->primaryImage->url) and !empty($edge->node->name->primaryImage->url)) {
                $imageUrl = $this->imageUrl($edge->node->name->primaryImage->url);
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

            $results[] = [
                'index' => $index,
                'id' => $imdbId,
                'url' => $this->baseUrl . "/name/" . $imdbId,
                'name' => $this->cleanString($name),
                'imageUrl' => $imageUrl,
                'professions' => $professions,
                'bio' => $bio,
            ];
            $index++;
        }

        return $results;
    }

    /**
     * Check if input birthdates are not empty and valid
     * @param string|null $startDate (searches between startDate and present date) iso date string ('1975-01-01')
     * @param string|null $endDate (searches between endDate and earlier) iso date string ('1975-01-01')
     * @return string|false Returns the constraints string or false if validation fails
     */
    private function checkBirthDates(?string $startDate, ?string $endDate): bool|string
    {
        if (empty($startDate) && empty($endDate)) {
            return false;
        }

        $constraint = 'birthDateConstraint: {';

        if (!empty($startDate) && !empty($endDate)) {
            if ($this->validateDate($startDate) === false || $this->validateDate($endDate) === false) {
                return false;
            }
            $constraint .= 'birthDateRange: {start:"' . $startDate . '", end:"' . $endDate . '"}';
        } elseif (!empty($startDate)) {
            if ($this->validateDate($startDate) === false) {
                return false;
            }
            $constraint .= 'birthDateRange: {start:"' . $startDate . '"}';
        } elseif (!empty($endDate)) {
            if ($this->validateDate($endDate) === false) {
                return false;
            }
            $constraint .= 'birthDateRange: {end:"' . $endDate . '"}';
        }

        $constraint .= '}';
        return $constraint;
    }

    /**
     * Check if input death dates are not empty and valid
     * @param string|null $startDate (searches between startDate and present date) iso date string ('1975-01-01')
     * @param string|null $endDate (searches between endDate and earlier) iso date string ('1975-01-01')
     * @return string|false Returns the constraints string or false if validation fails
     */
    private function checkDeathDates(?string $startDate, ?string $endDate): string|false
    {
        if (empty($startDate) && empty($endDate)) {
            return false;
        }

        $constraint = 'deathDateConstraint: {';

        if (!empty($startDate) && !empty($endDate)) {
            if ($this->validateDate($startDate) === false || $this->validateDate($endDate) === false) {
                return false;
            }
            $constraint .= 'deathDateRange: {start:"' . $startDate . '", end:"' . $endDate . '"}}';
        } elseif (!empty($startDate)) {
            if ($this->validateDate($startDate) === false) {
                return false;
            }
            $constraint .= 'deathDateRange: {start:"' . $startDate . '"}}';
        } elseif (!empty($endDate)) {
            if ($this->validateDate($endDate) === false) {
                return false;
            }
            $constraint .= 'deathDateRange: {end:"' . $endDate . '"}}';
        }

        return $constraint;
    }
}

