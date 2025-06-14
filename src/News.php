<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use InvalidArgumentException;

class News extends Base
{
    /**
     * Get the latest news for a specific category
     *
     * @param string $listType Category of news (MOVIE, TV, CELEBRITY, TOP, INDIE)
     * @param int $limit Number of news items to retrieve
     * @return array Array of news items
     * @throws InvalidArgumentException If invalid list type is provided
     * @throws Exception If GraphQL query fails
     */
    public function newsList(string $listType, int $limit = 250): array
    {
        $types = ['MOVIE', 'TV', 'CELEBRITY', 'TOP', 'INDIE'];
        if (!in_array($listType, $types)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid list type "%s". Valid types are: %s',
                    $listType,
                    implode(', ', $types)
                )
            );
        }

        $query = <<<GRAPHQL
query News {
  news(first: $limit, category: $listType) {
    edges {
      node {
        id
        articleTitle {
          plainText
        }
        byline
        date
        externalUrl
        image {
          url
          width
          height
        }
        source {
          homepage {
            label
            url
          }
        }
        text {
          plainText
          plaidHtml
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "News");

        $newsListItems = [];
        if (!isset($data->news->edges) || !is_array($data->news->edges)) {
            return $newsListItems;
        }

        foreach ($data->news->edges as $edge) {
            if (empty($edge->node->id) or empty($edge->node->articleTitle->plainText)) {
                continue;
            }

            $newsListItems[] = [
                'id' => $edge->node->id,
                'title' => $edge->node->articleTitle->plainText,
                'author' => $edge->node->byline ?? null,
                'date' => $edge->node->date ?? null,
                'source_url' => $edge->node->externalUrl ?? null,
                'source_home_url' => $edge->node->source->homepage->url ?? null,
                'source_label' => $edge->node->source->homepage->label ?? null,
                'plain_html' => $edge->node->text->plaidHtml ?? null,
                'plain_text' => $edge->node->text->plainText ?? null,
                'image' => $this->parseImage($edge->node->image)
            ];
        }

        return $newsListItems;
    }
}

