<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class CompanySearch extends Base
{
    /**
     * Search for companies matching input search string
     *
     * @param string $company Company name to search for (e.g. "warner brothers")
     * @param int $limit Maximum number of results to return (default: 50)
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     rank: array{
     *         current_rank: int|null,
     *         change_direction: string|null,
     *         difference: int|null
     *     },
     *     country: string|null,
     *     types: string[]
     * }>|array{} Return format:
     *     - Non-empty: A list of company results, each with:
     *         - 'id' (string): IMDb company ID
     *         - 'name' (string): Company name
     *         - 'rank' (array): Ranking information containing:
     *             - 'current_rank' (int|null): Current rank position
     *             - 'change_direction' (string|null): Direction of rank change (UP/DOWN)
     *             - 'difference' (int|null): Amount of rank change
     *         - 'country' (string|null): Country of origin
     *         - 'types' (string[]): Array of company types (e.g. ["Production", "Distributor"])
     *     - Empty array if no results or invalid input
     * @throws Exception On API request failure
     * @throws Exception On API request failure
     */
    public function search(string $company, int $limit = 50): array
    {
        $company = trim($company);
        if (empty($company)) {
            return [];
        }
        $searchTerm = '"' . $company . '"';

        $query = <<<GRAPHQL
query CompanySearch {
  mainSearch(
    first: {$limit}
    options: {
      searchTerm: {$searchTerm}
      type: COMPANY
      includeAdult: true
    }
  ) {
    edges {
      node {
        entity {
          ... on Company {
            id
            companyText {
              text
            }
            country {
              text
            }
            companyTypes {
              text
            }
            meterRanking {
              currentRank
              rankChange {
                changeDirection
                difference
              }
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

        $data = $this->graphql->query($query, "CompanySearch");

        if (!$this->hasArrayItems($data->mainSearch->edges)) {
            return [];
        }

        $results = [];

        foreach ($data->mainSearch->edges as $edge) {
            $e = $edge->node->entity ?? null;

            if (empty($e->id) or empty($e->companyText->text)) {
                continue;
            }

            // Company Types
            $types = [];
            if (isset($e->companyTypes)) {
                foreach ($e->companyTypes as $companyType) {
                    $types[] = $companyType->text;
                }
            }

            $results[] = [
                'id' => $e->id,
                'name' => $e->companyText->text,
                'rank' => [
                    'current_rank' => $e->meterRanking->currentRank ?? null,
                    'change_direction' => $e->meterRanking->rankChange->changeDirection ?? null,
                    'difference' => $e->meterRanking->rankChange->difference ?? null,
                ],
                'country' => $e->country->text ?? null,
                'types' => $types
            ];
        }

        return $results;
    }
}