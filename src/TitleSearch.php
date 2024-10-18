<?php

namespace Hooshid\ImdbScraper;

use DateTime;
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
            'searchTerm' => '',
            'types' => '',
            'genres' => '',
            'creditId' => '',
            'startDate' => '',
            'endDate' => '',
            'countries' => '',
            'languages' => '',
            'adult' => 'EXCLUDE_ADULT',
            'limit' => 50,
            'sortBy' => 'POPULARITY',
            'sortOrder' => 'ASC'
        ];

        // Merge the defaults with the incoming parameters
        $params = array_merge($defaults, $params);

        $results = [];

        // Extract the parameters & check and validate input parameters
        $inputSearchTerm = $this->checkSearchTerm($params['searchTerm']);
        $inputGenres = $this->checkItems($params['genres']);
        $inputTypes = $this->checkItems($params['types']);
        $inputCreditId = $this->checkItems($params['creditId']);
        $inputReleaseDates = $this->checkReleaseDates($params['startDate'], $params['endDate']);
        $inputCountries = $this->checkItems($params['countries']);
        $inputLanguages = $this->checkItems($params['languages']);
        $adult = strtoupper($params['adult']);
        // check releasedate valid or not, array() otherwise
        if ($inputReleaseDates === false) {
            return $results;
        }

        // check if there is at least one valid input parameter, array() otherwise
        if ($inputSearchTerm == "null" &&
            empty($inputGenres) &&
            empty($inputTypes) &&
            empty($inputCreditId) &&
            $inputReleaseDates["startDate"] == "null" &&
            $inputReleaseDates["endDate"] == "null" &&
            empty($inputCountries) &&
            empty($inputLanguages)
        ) {
            return $results;
        }

        $limit = (int)$params['limit'];
        $sortBy = strtoupper($params['sortBy']);
        $sortOrder = strtoupper($params['sortOrder']);


        $query = <<<EOF
query advancedSearch{
  advancedTitleSearch(
    first: $limit, sort: {sortBy: $sortBy sortOrder: $sortOrder}
    constraints: {
      titleTextConstraint: {searchTerm: $inputSearchTerm}
      genreConstraint: {allGenreIds: [$inputGenres]}
      titleTypeConstraint: {anyTitleTypeIds: [$inputTypes]}
      releaseDateConstraint: {releaseDateRange: {start: $inputReleaseDates[startDate] end: $inputReleaseDates[endDate]}}
      creditedNameConstraint: {anyNameIds: [$inputCreditId]}
      originCountryConstraint: {anyCountries: [$inputCountries]}
      languageConstraint: {anyLanguages: [$inputLanguages]}
      explicitContentConstraint: {explicitContentFilter: $adult}
    }
  ) {
    edges {
      node{
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
          }
        }
      }
    }
  }
}
EOF;

        try {
            $data = $this->graphql->query($query, "advancedSearch");
        } catch (Exception $e) {
            return $results;
        }

        foreach ($data->advancedTitleSearch->edges as $edge) {
            $imdbId = $edge->node->title->id ?? '';
            $originalTitle = $edge->node->title->titleText->text ?? '';

            if (empty($imdbId) or empty($originalTitle)) {
                continue;
            }

            $title = $edge->node->title->titleText->text ?? '';
            $type = $edge->node->title->titleType->text ?? '';

            $yearRange = '';
            if (isset($edge->node->title->releaseYear->year)) {
                $yearRange .= $edge->node->title->releaseYear->year;
                if (isset($edge->node->title->releaseYear->endYear)) {
                    $yearRange .= '-' . $edge->node->title->releaseYear->endYear;
                }
            }

            // Image url
            $imageUrl = null;
            if (isset($edge->node->title->primaryImage->url) and !empty($edge->node->title->primaryImage->url)) {
                $imageUrl = $this->imageUrl($edge->node->title->primaryImage->url);
            }

            $results[] = [
                'id' => $imdbId,
                'url' => $this->baseUrl . "/title/" . $imdbId,
                'originalTitle' => $originalTitle,
                'title' => $title,
                'type' => $type,
                'year' => $yearRange,
                'imageUrl' => $imageUrl
            ];
        }

        return $results;
    }

    /**
     * Check if there is at least one, possible more input items
     *
     * @param string $items
     * @return string
     */
    private function checkItems(string $items): string
    {
        if (empty(trim($items))) {
            return '';
        }
        if (stripos($items, ',') !== false) {
            $itemsParts = explode(",", $items);
            $itemsOutput = '"';
            foreach ($itemsParts as $key => $value) {
                $itemsOutput .= trim($value);
                end($itemsParts);
                if ($key !== key($itemsParts)) {
                    $itemsOutput .= '","';
                } else {
                    $itemsOutput .= '"';
                }

            }
            return $itemsOutput;
        } else {
            return '"' . trim($items) . '"';
        }
    }

    /**
     * Check searchTerm
     *
     * @param string $searchTerm
     * @return string
     */
    private function checkSearchTerm(string $searchTerm): string
    {
        if (empty(trim($searchTerm))) {
            return "null";
        } else {
            return '"' . trim($searchTerm) . '"';
        }
    }

    /**
     * Check if provided date is valid
     *
     * @param string $date
     * @return bool
     */
    private function validateDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Check if input date is not empty and valid
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array|bool
     */
    private function checkReleaseDates(?string $startDate, ?string $endDate): array|bool
    {
        $startDate = trim($startDate);
        $endDate = trim($endDate);

        // Both dates are empty
        if (empty($startDate) && empty($endDate)) {
            return [
                'startDate' => "null",
                'endDate' => "null"
            ];
        }

        // Validate date if not empty
        $validStart = empty($startDate) || $this->validateDate($startDate) !== false;
        $validEnd = empty($endDate) || $this->validateDate($endDate) !== false;

        if (!$validStart || !$validEnd) {
            return false;
        }

        // Return formatted dates
        return [
            'startDate' => empty($startDate) ? "null" : '"' . $startDate . '"',
            'endDate' => empty($endDate) ? "null" : '"' . $endDate . '"'
        ];
    }

}

