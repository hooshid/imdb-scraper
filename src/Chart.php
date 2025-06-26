<?php

namespace Hooshid\ImdbScraper;

use DateTime;
use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Chart extends Base
{
    /**
     * Get the USA Weekend Box-Office Summary including weekend and lifetime earnings (https://www.imdb.com/chart/boxoffice/)
     *
     * Retrieves top 10 box office results from IMDB (maximum available)
     *
     * @return array{
     *     weekend_start_date: string|null,
     *     weekend_end_date: string|null,
     *     list: array<int, array{
     *         id: string,
     *         title: string,
     *         rating: float|null,
     *         votes: int|null,
     *         lifetime_gross_amount: int|null,
     *         lifetime_gross_currency: string|null,
     *         weekend_gross_amount: int|null,
     *         weekend_gross_currency: string|null,
     *         weeks_released: int|null,
     *         image: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }|null
     *     }>
     * } Returns box office data with:
     *     - Weekend date range (start and end dates in YYYY-MM-DD format)
     *     - List of top 10 movies containing:
     *         - Movie ID and title
     *         - Rating and vote count
     *         - Lifetime gross earnings (amount and currency)
     *         - Weekend gross earnings (amount and currency)
     *         - Weeks since release
     *         - Primary image with dimensions
     * @throws Exception If API request fails
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

        if (!$this->hasArrayItems($data->boxOfficeWeekendChart->entries)) {
            return [];
        }

        $list = [];

        foreach ($data->boxOfficeWeekendChart->entries as $edge) {
            if (empty($edge->title->id) || empty($edge->title->titleText->text)) {
                continue;
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
                'weeks_released' => $this->calculateWeeksReleased($edge->title->releaseDate ?? null),
                'image' => $this->parseImage($edge->title->primaryImage ?? null)
            );
        }

        return [
            'weekend_start_date' => $data->boxOfficeWeekendChart->weekendStartDate ?? null,
            'weekend_end_date' => $data->boxOfficeWeekendChart->weekendEndDate ?? null,
            'list' => $list
        ];
    }

    /**
     * Calculate weeks since release date
     *
     * @param object|null $releaseDate
     * @return int|null
     */
    private function calculateWeeksReleased(?object $releaseDate): ?int
    {
        if (empty($releaseDate->day) || empty($releaseDate->month) || empty($releaseDate->year)) {
            return null;
        }

        $startDate = "{$releaseDate->month}/{$releaseDate->day}/{$releaseDate->year}";
        return $this->dateDiffInWeeks($startDate, date('m/d/Y'));
    }

    /**
     * Calculate difference in weeks between two dates
     *
     * @param string $startDate Format: 'm/d/Y' (e.g. '1/2/2013')
     * @param string $endDate Format: 'm/d/Y' (e.g. '1/2/2013')
     * @return int Number of weeks between dates
     */
    private function dateDiffInWeeks(string $startDate, string $endDate): int
    {
        if ($startDate > $endDate) {
            return $this->dateDiffInWeeks($endDate, $startDate);
        }

        $first = DateTime::createFromFormat('m/d/Y', $startDate);
        $second = DateTime::createFromFormat('m/d/Y', $endDate);

        return (int)ceil($first->diff($second)->days / 7);
    }

    /**
     * Get IMDb top lists
     *
     * @param string $listType Type of list to retrieve. Valid values:
     *     - 'TOP_250' - Top 250 Movies
     *     - 'TOP_250_TV' - Top 250 TV Shows
     *     - 'TOP_250_ENGLISH' - Top 250 English Movies
     *     - 'TOP_250_INDIA' - Top 250 Indian Movies
     *     - 'TOP_50_TELUGU' - Top 50 Telugu Movies
     *     - 'TOP_50_TAMIL' - Top 50 Tamil Movies
     *     - 'TOP_50_MALAYALAM' - Top 50 Malayalam Movies
     *     - 'TOP_50_BENGALI' - Top 50 Bengali Movies
     *     - 'BOTTOM_100' - Bottom 100 Movies
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     rank: int,
     *     type: string|null,
     *     runtime_minutes: int|null,
     *     runtime_seconds: int|null,
     *     year: int|null,
     *     rating: float|null,
     *     votes: int|null,
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }> Returns list of ranked titles with:
     *     - 'id': IMDb title ID
     *     - 'title': Title name
     *     - 'rank': Current ranking position
     *     - 'type': Title type (Movie/TV/etc)
     *     - 'runtime_minutes': Runtime in minutes
     *     - 'runtime_seconds': Runtime in seconds
     *     - 'year': Release year
     *     - 'rating': Average rating
     *     - 'votes': Number of votes
     *     - 'image': Primary image with dimensions
     * @throws Exception If API request fails
     */
    public function getList(string $listType): array
    {
        $types = [
            'TOP_250',
            'TOP_250_TV',
            'TOP_250_ENGLISH',
            'TOP_250_INDIA',
            'TOP_50_TELUGU',
            'TOP_50_TAMIL',
            'TOP_50_MALAYALAM',
            'TOP_50_BENGALI',
            'BOTTOM_100'
        ];
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

        if (!$this->hasArrayItems($data->titleChartRankings->edges)) {
            return [];
        }

        $list = [];

        foreach ($data->titleChartRankings->edges as $edge) {
            if (empty($edge->node->item->id) || empty($edge->node->item->titleText->text)) {
                continue;
            }

            $list[] = [
                'id' => $edge->node->item->id,
                'title' => $edge->node->item->titleText->text,
                'rank' => $edge->node->item->ratingsSummary->topRanking->rank ?? 0,
                'type' => $edge->node->item->titleType->text ?? null,
                'runtime_minutes' => $this->secondsToMinutes($edge->node->item->runtime->seconds ?? null),
                'runtime_seconds' => $edge->node->item->runtime->seconds ?? null,
                'year' => $edge->node->item->releaseYear->year ?? null,
                'rating' => $edge->node->item->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->node->item->ratingsSummary->voteCount ?? null,
                'image' => $this->parseImage($edge->node->item->primaryImage ?? null)
            ];
        }

        return $list;
    }

    /**
     * Get most popular titles from IMDb charts (https://imdb.com/chart/moviemeter)
     *
     * @param string $listType Type of chart to retrieve. Valid values:
     *     - 'MOST_POPULAR_MOVIES'
     *     - 'TOP_RATED_MOVIES'
     *     - 'LOWEST_RATED_MOVIES'
     *     - 'TOP_RATED_ENGLISH_MOVIES'
     *     - 'MOST_POPULAR_TV_SHOWS'
     *     - 'TOP_RATED_TV_SHOWS'
     * @param string|null $genreId Optional genre filter. Available genres:
     *     Action, Adult, Adventure, Animation, Biography, Comedy, Crime,
     *     Documentary, Drama, Family, Fantasy, Film-Noir, Game-Show, History,
     *     Horror, Music, Musical, Mystery, News, Reality-TV, Romance, Sci-Fi,
     *     Short, Sport, Talk-Show, Thriller, War, Western
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     rank: int,
     *     type: string|null,
     *     runtime_minutes: int|null,
     *     runtime_seconds: int|null,
     *     genres: string[],
     *     year: int|null,
     *     rating: float|null,
     *     votes: int|null,
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }> Returns list of popular titles with:
     *     - 'id': IMDb title ID
     *     - 'title': Title name
     *     - 'rank': Current popularity rank
     *     - 'type': Title type (Movie/TV/etc)
     *     - 'runtime_minutes': Runtime in minutes
     *     - 'runtime_seconds': Runtime in seconds
     *     - 'genres': Array of genre names
     *     - 'year': Release year
     *     - 'rating': Average rating
     *     - 'votes': Number of votes
     *     - 'image': Primary image with dimensions
     * @throws Exception If API request fails
     */
    public function getMostPopularTitles(string $listType, string $genreId = null): array
    {
        $types = [
            'MOST_POPULAR_MOVIES',
            'TOP_RATED_MOVIES',
            'LOWEST_RATED_MOVIES',
            'TOP_RATED_ENGLISH_MOVIES',
            'MOST_POPULAR_TV_SHOWS',
            'TOP_RATED_TV_SHOWS'
        ];
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

        if (!$this->hasArrayItems($data->chartTitles->edges)) {
            return [];
        }

        $list = [];

        foreach ($data->chartTitles->edges as $edge) {
            if (empty($edge->node->id) or empty($edge->node->titleText->text)) {
                continue;
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
                'runtime_minutes' => $this->secondsToMinutes($edge->node->runtime->seconds ?? null),
                'runtime_seconds' => $edge->node->runtime->seconds ?? null,
                'genres' => $genres,
                'year' => $edge->node->releaseYear->year ?? null,
                'rating' => $edge->node->ratingsSummary->aggregateRating ?? null,
                'votes' => $edge->node->ratingsSummary->voteCount ?? null,
                'image' => $this->parseImage($edge->node->primaryImage)
            ];
        }

        return $list;
    }

    /**
     * Get most popular names from IMDb starmeter chart
     *
     * Retrieves the top 100 most popular names/celebrities as seen on
     * https://imdb.com/chart/starmeter
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     rank: int|null,
     *     professions: string[],
     *     known_for: array<int, array{
     *         id: string,
     *         title: string,
     *         year: int|null,
     *         end_year: int|null
     *     }>,
     *     image: array{
     *         url: string,
     *         width: int,
     *         height: int
     *     }|null
     * }> Returns list of popular names with:
     *     - 'id': IMDb name ID
     *     - 'name': Full name
     *     - 'rank': Current starmeter rank
     *     - 'professions': Array of profession categories
     *     - 'known_for': Array of notable works with:
     *         - 'id': Title ID
     *         - 'title': Title name
     *         - 'year': Release year
     *         - 'end_year': End year (for series)
     *     - 'image': Primary image with dimensions
     * @throws Exception If API request fails
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
        meterRanking {
          currentRank
          rankChange {
            difference
            changeDirection
          }
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
                releaseYear {
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
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "MostPopularName");

        $items = [];
        if (!$this->hasArrayItems($data->chartNames->edges)) {
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
                    if (empty($known->node->title->id) || empty($known->node->title->titleText->text)) {
                        continue;
                    }

                    $knownFor[] = [
                        'id' => $known->node->title->id,
                        'title' => $known->node->title->titleText->text,
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
                'image' => $this->parseImage($edge->node->primaryImage)
            ];
        }

        return $items;
    }

}

