<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Chart extends Base
{
    /**
     * Get the USA Weekend Box-Office Summary, weekend earnings and all time earnings
     *
     * @return array
     */
    public function getBoxOffice(): array
    {
        $oldBase = new \Hooshid\ImdbScraper\Base\Old\Base();
        $dom = $oldBase->getHtmlDomParser("/chart/boxoffice/");

        $boxoffice = [];
        $i = 0;
        foreach ($dom->find('[data-testid="chart-layout-main-column"] ul li') as $e) {
            $id = $oldBase->getImdbId($e->find('.ipc-title a', 0)->getAttribute('href'));
            if ($id) {
                $boxoffice[$i]['id'] = $id;
                $title = $this->cleanString($e->find('.ipc-title h3', 0)->innerText());
                $boxoffice[$i]['title'] = preg_replace('/^\d+\.\s/', '', $title);

                $moneyPattern = "/[\$£]([\d\.]+)(M|K)/";
                foreach ($e->find('ul[data-testid="title-metadata-box-office-data-container"] li') as $metadata) {
                    $cellTitle = $metadata->find('span', 0)->innerText();
                    if (str_contains($cellTitle, 'Weeks Released')) {
                        $boxoffice[$i]['weeks'] = $this->cleanString($metadata->find('span', 1)->innerText());
                    } else if (str_contains($cellTitle, 'Weekend Gross')) {
                        // Weekend
                        $weekend = $this->cleanString($metadata->find('span', 1)->innerText());
                        $weekendMatches = null;
                        preg_match($moneyPattern, $weekend, $weekendMatches, PREG_OFFSET_CAPTURE);
                        $boxoffice[$i]['weekend'] = $weekendMatches[2][0] === 'M' ? $weekendMatches[1][0] : $weekendMatches[1][0] / 1000;
                    } else if (str_contains($cellTitle, 'Total Gross')) {
                        // Gross
                        $gross = $this->cleanString($metadata->find('span', 1)->innerText());
                        $grossMatches = null;
                        preg_match($moneyPattern, $gross, $grossMatches, PREG_OFFSET_CAPTURE);
                        $boxoffice[$i]['gross'] = $grossMatches[2][0] === 'M' ? $grossMatches[1][0] : $grossMatches[1][0] / 1000;
                    }
                }
                $i++;
            }
        }

        return $boxoffice;
    }

    /**
     * Get IMDb top lists
     *
     * @param string $listType
     * @return array
     */
    public function getList(string $listType): array
    {
        $types = ['TOP_250', 'TOP_250_TV', 'TOP_250_ENGLISH', 'TOP_250_INDIA', 'TOP_50_TELUGU', 'TOP_50_TAMIL', 'TOP_50_MALAYALAM', 'TOP_50_BENGALI', 'BOTTOM_100'];
        if (!in_array($listType, $types)) {
            return [];
        }

        $query = <<<EOF
query {
  titleChartRankings(
    first: 250
    input: {rankingsChartType: $listType}
  ) {
    edges {
      node{
        item {
          id
          titleText {
            text
          }
          titleType {
            text
          }
          releaseYear {
            year
          }
          ratingsSummary {
            topRanking {
              rank
            }
            aggregateRating
            voteCount
          }
          primaryImage {
            url
          }
          runtime {
            seconds
            displayableProperty {
              value {
                plainText
              }
            }
          }
        }
      }
    }
  }
}
EOF;
        $list = [];
        try {
            $data = $this->graphql->query($query);
        } catch (Exception $e) {
            return $list;
        }

        foreach ($data->titleChartRankings->edges as $edge) {
            $imdbId = $edge->node->item->id ?? '';
            $title = $edge->node->item->titleText->text ?? '';

            if (empty($imdbId) or empty($title)) {
                continue;
            }

            $rank = $edge->node->item->ratingsSummary->topRanking->rank ?? null;
            $year = $edge->node->item->releaseYear->year ?? null;
            $rating = $edge->node->item->ratingsSummary->aggregateRating ?? null;
            $votes = $edge->node->item->ratingsSummary->voteCount ?? null;

            // Image url
            $imageUrl = null;
            if (isset($edge->node->item->primaryImage->url) and !empty($edge->node->item->primaryImage->url)) {
                $imageUrl = $this->imageUrl($edge->node->item->primaryImage->url);
            }

            $list[] = array(
                'rank' => (int)$rank,
                'id' => $imdbId,
                'title' => $title,
                'type' => $edge->node->item->titleType->text,
                'imageUrl' => $imageUrl,
                'year' => $year,
                'rating' => $rating,
                'votes' => $votes
            );
        }

        return $list;
    }

}

