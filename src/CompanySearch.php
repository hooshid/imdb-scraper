<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class CompanySearch extends Base
{
    /**
     * Search companies
     *
     * @param string $company
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function search(string $company, int $limit = 50): array
    {
        if (empty(trim($company))) {
            return [];
        }
        $inputCompany = '"' . trim($company) . '"';

        $query = <<<GRAPHQL
query CompanySearch {
  mainSearch(
    first: $limit
    options: {
      searchTerm: $inputCompany
      type: COMPANY
      includeAdult: true
    }
  ) {
    edges {
      node {
        entity {
          ... on Company {
            id
            country {
              text
            }
            companyText {
              text
            }
            companyTypes {
              text
            }
            meterRanking {
              currentRank
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

        $data = $this->graphql->query($query, "CompanySearch");

        if (!isset($data->mainSearch->edges) || !is_array($data->mainSearch->edges)) {
            return [];
        }

        $results = [];

        foreach ($data->mainSearch->edges as $edge) {
            if (empty($edge->node->entity->id) or empty($edge->node->entity->companyText->text)) {
                continue;
            }

            // Company Types
            $types = [];
            if (isset($edge->node->entity->companyTypes)) {
                foreach ($edge->node->entity->companyTypes as $companyType) {
                    $types[] = $companyType->text;
                }
            }

            $results[] = [
                'id' => $edge->node->entity->id,
                'name' => $edge->node->entity->companyText->text,
                'rank' => $edge->node->entity->meterRanking->currentRank ?? null,
                'country' => $edge->node->entity->country->text ?? null,
                'types' => $types
            ];
        }

        return $results;
    }
}