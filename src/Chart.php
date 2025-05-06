<?php

namespace Hooshid\ImdbScraper;

use DateTime;
use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Chart extends Base
{
    /**
     * Get the USA Weekend Box-Office Summary, weekend earnings and all time earnings
     *
     * @return array
     * @throws Exception
     */
    public function getBoxOffice(): array
    {
        $query = <<<GRAPHQL
query BoxOffice {
  boxOfficeWeekendChart(limit: 10) {
    entries {
      title {
        id
        titleText {
          text
        }
        releaseDate {
          day
          month
          year
        }
        ratingsSummary {
          aggregateRating
          voteCount
        }
        primaryImage {
          url
          width
          height
        }
        lifetimeGross(boxOfficeArea: DOMESTIC) {
          total {
            amount
            currency
          }
        }
      }
      weekendGross {
        total {
          amount
          currency
        }
      }
    }
    weekendEndDate
    weekendStartDate
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "BoxOffice");

        $list = [];
        if (!isset($data->boxOfficeWeekendChart->entries) || !is_array($data->boxOfficeWeekendChart->entries)) {
            return $list;
        }

        foreach ($data->boxOfficeWeekendChart->entries as $edge) {
            if (empty($edge->title->id) or empty($edge->title->titleText->text)) {
                continue;
            }

            $weeks = null;
            if (!empty($edge->title->releaseDate->day) && !empty($edge->title->releaseDate->month) && !empty($edge->title->releaseDate->year)) {
                $startDate = $edge->title->releaseDate->month . '/' .
                    $edge->title->releaseDate->day . '/' .
                    $edge->title->releaseDate->year;
                $weeks = $this->datediffInWeeks($startDate, date('m/d/Y'));
            }

            $list[] = array(
                'id' => $edge->title->id,
                'title' => $edge->title->titleText->text,
                'rating' => $edge->title->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->title->ratingsSummary->voteCount ?? null,
                'lifetime_gross_amount' => $edge->title->lifetimeGross->total->amount ?? null,
                'lifetime_gross_currency' => $edge->title->lifetimeGross->total->currency ?? null,
                'weekend_gross_amount' => $edge->weekendGross->total->amount ?? null,
                'weekend_gross_currency' => $edge->weekendGross->total->currency ?? null,
                'weeks_released' => $weeks,
                'image' => $this->image($edge->title->primaryImage)
            );
        }

        return [
            'weekend_start_date' => $data->boxOfficeWeekendChart->weekendStartDate ?? null,
            'weekend_end_date' => $data->boxOfficeWeekendChart->weekendEndDate ?? null,
            'list' => $list
        ];
    }

    /**
     * Get amount of weeks between input date and current date
     *
     * @param string $startDate like '1/2/2013' (month/day/year)
     * @param string $endDate current date! like '1/2/2013' (month/day/year)
     * @return int number of weeks
     */
    private function dateDiffInWeeks(string $startDate, string $endDate): int
    {
        if ($startDate > $endDate) return $this->dateDiffInWeeks($endDate, $startDate);
        $first = DateTime::createFromFormat('m/d/Y', $startDate);
        $second = DateTime::createFromFormat('m/d/Y', $endDate);
        return ceil($first->diff($second)->days / 7);
    }

    /**
     * Get IMDb top lists
     *
     * @param string $listType
     * @return array
     * @throws Exception
     */
    public function getList(string $listType): array
    {
        $types = ['TOP_250', 'TOP_250_TV', 'TOP_250_ENGLISH', 'TOP_250_INDIA', 'TOP_50_TELUGU', 'TOP_50_TAMIL', 'TOP_50_MALAYALAM', 'TOP_50_BENGALI', 'BOTTOM_100'];
        if (!in_array($listType, $types)) {
            return [];
        }

        $query = <<<GRAPHQL
query Top250Title {
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
            width
            height
          }
          runtime {
            seconds
          }
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "Top250Title");

        $list = [];
        if (!isset($data->titleChartRankings->edges) || !is_array($data->titleChartRankings->edges)) {
            return $list;
        }

        foreach ($data->titleChartRankings->edges as $edge) {
            if (empty($edge->node->item->id) or empty($edge->node->item->titleText->text)) {
                continue;
            }

            $runtime = null;
            if (!empty($edge->node->item->runtime->seconds)) {
                $runtime = $edge->node->item->runtime->seconds / 60;
            }

            $list[] = array(
                'id' => $edge->node->item->id,
                'title' => $edge->node->item->titleText->text,
                'rank' => $edge->node->item->ratingsSummary->topRanking->rank ?? 0,
                'type' => $edge->node->item->titleType->text ?? null,
                'runtime' => $runtime,
                'year' => $edge->node->item->releaseYear->year ?? null,
                'rating' => $edge->node->item->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->node->item->ratingsSummary->voteCount ?? null,
                'image' => $this->image($edge->node->item->primaryImage)
            );
        }

        return $list;
    }

    /**
     * Get most popular Titles lists as seen on https://imdb.com/chart/moviemeter
     *
     * @param string $listType This defines different kind of lists like Movie or TV
     * @param string|null $genreId This filters the results on a genreId like "Horror"
     * GenreIDs: Action, Adult, Adventure, Animation, Biography, Comedy, Crime,
     *           Documentary, Drama, Family, Fantasy, Film-Noir, Game-Show,
     *           History, Horror, Music, Musical, Mystery, News, Reality-TV,
     *           Romance, Sci-Fi, Short, Sport, Talk-Show, Thriller, War, Western
     *
     * @return array
     * @throws Exception
     */
    public function getMostPopularTitles(string $listType, string $genreId = null): array
    {
        $types = ['MOST_POPULAR_MOVIES', 'TOP_RATED_MOVIES', 'LOWEST_RATED_MOVIES', 'TOP_RATED_ENGLISH_MOVIES', 'MOST_POPULAR_TV_SHOWS', 'TOP_RATED_TV_SHOWS'];
        if (!in_array($listType, $types)) {
            return [];
        }

        $filter = '';
        if (!empty($genreId)) {
            $filter = 'genreConstraint:{allGenreIds:["' . $genreId . '"]}';
        }

        $query = <<<GRAPHQL
query MostPopularTitle {
  chartTitles(
    first: 9999
    chart: {chartType: $listType}
    sort: {sortBy: RANKING, sortOrder: ASC}
    filter:{explicitContentConstraint:{explicitContentFilter:INCLUDE_ADULT}$filter}
  ) {
    edges {
      currentRank
      node {
        id
        titleGenres {
          genres {
            genre {
              text
            }
          }
        }
        titleText {
          text
        }
        titleType {
          text
        }
        releaseYear {
          year
        }
        runtime {
          seconds
        }
        ratingsSummary {
          aggregateRating
          voteCount
        }
        primaryImage {
          url
          width
          height
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "MostPopularTitle");

        $list = [];
        if (!isset($data->chartTitles->edges) || !is_array($data->chartTitles->edges)) {
            return $list;
        }

        foreach ($data->chartTitles->edges as $edge) {
            if (empty($edge->node->id) or empty($edge->node->titleText->text)) {
                continue;
            }

            $runtime = null;
            if (!empty($edge->node->runtime->seconds)) {
                $runtime = $edge->node->runtime->seconds / 60;
            }

            $genres = [];
            if (!empty($edge->node->titleGenres->genres)) {
                foreach ($edge->node->titleGenres->genres as $genre) {
                    if (!empty($genre->genre->text)) {
                        $genres[] = $genre->genre->text;
                    }
                }
            }

            $list[] = [
                'id' => $edge->node->id,
                'title' => $edge->node->titleText->text,
                'rank' => $edge->currentRank ?? 0,
                'type' => $edge->node->titleType->text ?? null,
                'runtime' => $runtime,
                'genres' => $genres,
                'year' => $edge->node->releaseYear->year ?? null,
                'rating' => $edge->node->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->node->ratingsSummary->voteCount ?? null,
                'image' => $this->image($edge->node->primaryImage)
            ];
        }

        return $list;
    }

    /**
     * Get most popular Names lists as seen on https://imdb.com/chart/starmeter
     *
     * @return array
     * @throws Exception
     */
    public function getMostPopularNames(): array
    {
        $query = <<<GRAPHQL
query MostPopularName {
  chartNames(
    first: 100
    chart: {chartType: MOST_POPULAR_NAMES}
    sort: {sortBy: POPULARITY, sortOrder: ASC}
  ) {
    edges {
      node {
        id
        nameText {
          text
        }
        creditCategories {
          category {
            text
          }
        }
        knownFor(first: 5) {
          edges {
            node {
              title {
                id
                titleText {
                  text
                }
                releaseYear{
                  year
                  endYear
                }
              }
            }
          }
        }
        primaryImage {
          url
          width
          height
        }
        meterRanking {
          currentRank
          rankChange {
            difference
            changeDirection
          }
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "MostPopularName");

        $items = [];
        if (!isset($data->chartNames->edges) || !is_array($data->chartNames->edges)) {
            return $items;
        }

        foreach ($data->chartNames->edges as $edge) {
            if (empty($edge->node->id) or empty($edge->node->nameText->text)) {
                continue;
            }

            // knownFor
            $knownFor = [];
            if (!empty($edge->node->knownFor->edges)) {
                foreach ($edge->node->knownFor->edges as $known) {
                    $knownFor[] = [
                        'id' => $known->node->title->id ?? null,
                        'title' => $known->node->title->titleText->text ?? null,
                        'year' => $known->node->title->releaseYear->year ?? null,
                        'end_year' => $known->node->title->releaseYear->endYear ?? null
                    ];
                }
            }

            // Professions
            $professions = [];
            if (isset($edge->node->creditCategories)) {
                foreach ($edge->node->creditCategories as $profession) {
                    $professions[] = $profession->category->text;
                }
            }

            $items[] = [
                'id' => $edge->node->id,
                'name' => $edge->node->nameText->text,
                'rank' => $edge->node->meterRanking->currentRank ?? null,
                'professions' => $professions,
                'known_for' => $knownFor,
                'image' => $this->image($edge->node->primaryImage)
            ];
        }

        return $items;
    }

}

