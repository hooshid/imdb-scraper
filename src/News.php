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
     * @param int $limit Number of news items to retrieve (maximum 250)
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     author: string|null,
     *     date: string|null,
     *     source_url: string|null,
     *     source_home_url: string|null,
     *     source_label: string|null,
     *     plain_html: string|null,
     *     plain_text: string|null,
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }>|array{} Return format:
     *     - Non-empty: A list of news articles with:
     *         - 'id' (string): Unique identifier for the news article
     *         - 'title' (string): Article headline
     *         - 'author' (string|null): Author byline if available
     *         - 'date' (string|null): Publication date in Y-m-d H:i:s format
     *         - 'source_url' (string|null): Direct URL to the article
     *         - 'source_home_url' (string|null): Publisher's homepage URL
     *         - 'source_label' (string|null): Name of the news source
     *         - 'plain_html' (string|null): Formatted HTML content
     *         - 'plain_text' (string|null): Plain text content
     *         - 'image' (array|null): Associated image with URL and dimensions
     *     - Empty array if no news found or invalid parameters
     * @throws InvalidArgumentException If invalid list type is provided
     * @throws Exception If GraphQL query fails
     */
    public function newsList(string $listType, int $limit = 250): array
    {
        $this->validateListType($listType);

        $query = <<<GRAPHQL
query News {
  news(
    first: {$limit},
    category: {$listType}
  ) {
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

        if (!$this->hasArrayItems($data->news->edges)) {
            return [];
        }

        return $this->parseNewsResults($data->news->edges);
    }

    /**
     * Validate the news category type
     *
     * @throws InvalidArgumentException
     */
    private function validateListType(string $listType): void
    {
        $validTypes = ['MOVIE', 'TV', 'CELEBRITY', 'TOP', 'INDIE'];

        if (!in_array($listType, $validTypes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid list type "%s". Valid types are: %s',
                    $listType,
                    implode(', ', $validTypes)
                )
            );
        }
    }

    /**
     * Process news results from GraphQL response
     *
     * @param array<object> $edges
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     author: string|null,
     *     date: string|null,
     *     source_url: string|null,
     *     source_home_url: string|null,
     *     source_label: string|null,
     *     plain_html: string|null,
     *     plain_text: string|null,
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }>|array{}
     */
    public function parseNewsResults(array $edges): array
    {
        $results = [];

        foreach ($edges as $edge) {
            $node = $edge->node ?? null;

            if (empty($node->id) || empty($node->articleTitle->plainText)) {
                continue;
            }

            $results[] = [
                'id' => $node->id,
                'title' => $node->articleTitle->plainText,
                'author' => $node->byline ?? null,
                'date' => $node->date ? $this->reformatDate($node->date) : null,
                'source_url' => $node->externalUrl ?? null,
                'source_home_url' => $node->source->homepage->url ?? null,
                'source_label' => $node->source->homepage->label ?? null,
                'plain_html' => $node->text->plaidHtml ?? null,
                'plain_text' => $node->text->plainText ?? null,
                'image' => $this->parseImage($node->image ?? null)
            ];
        }

        return $results;
    }
}

