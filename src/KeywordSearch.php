<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class KeywordSearch extends Base
{
    /**
     * Search keyword
     *
     * @param string $keyword
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function search(string $keyword, int $limit = 50): array
    {
        if (empty(trim($keyword))) {
            return [];
        }
        $inputKeywords = '"' . trim($keyword) . '"';

        $query = <<<GRAPHQL
query SearchKeyword {
  mainSearch(
    first: $limit
    options: {
      searchTerm: $inputKeywords
      type: KEYWORD
      includeAdult: true
    }
  ) {
    edges {
      node {
        entity {
          ... on Keyword {
            id
            text {
              text
            }
            titles(first: 9999) {
              total
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

        $data = $this->graphql->query($query, "SearchKeyword");

        if (!isset($data->mainSearch->edges) || !is_array($data->mainSearch->edges)) {
            return [];
        }

        $results = [];

        foreach ($data->mainSearch->edges as $edge) {
            if (empty($edge->node->entity->id) or empty($edge->node->entity->text->text)) {
                continue;
            }

            $results[] = [
                'id' => $edge->node->entity->id,
                'keyword' => $edge->node->entity->text->text,
                'total_titles' => $edge->node->entity->titles->total ?? null
            ];
        }

        return $results;
    }
}