<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class KeywordSearch extends Base
{
    /**
     * Search keywords
     *
     * @param string $keyword The search term to look up
     * @param int $limit Maximum number of results to return (default: 50)
     * @return array<int, array{id: string, keyword: string, total_titles: int}>|array{} Return format:
     *     - Non-empty: A list of keyword results, each with:
     *         - 'id' (string): IMDb keyword ID
     *         - 'keyword' (string): Original keyword text
     *         - 'total_titles' (int): Count of associated titles
     *     - Empty array if no results
     * @throws Exception On API request failure
     */
    public function search(string $keyword, int $limit = 50): array
    {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return [];
        }
        $searchTerm = '"' . $keyword . '"';

        $query = <<<GRAPHQL
query SearchKeyword {
  mainSearch(
    first: {$limit}
    options: {
      searchTerm: {$searchTerm}
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

        if (!isset($data->mainSearch->edges) || !is_array($data->mainSearch->edges) || count($data->mainSearch->edges) === 0) {
            return [];
        }

        $results = [];

        foreach ($data->mainSearch->edges as $edge) {
            $entity = $edge->node->entity ?? null;

            if (empty($entity->id) || empty($entity->text->text) || empty($entity->titles->total)) {
                continue;
            }

            $results[] = [
                'id' => $entity->id,
                'keyword' => $entity->text->text,
                'total_titles' => $entity->titles->total
            ];
        }

        return $results;
    }
}