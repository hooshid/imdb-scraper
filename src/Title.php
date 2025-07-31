<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Title extends Base
{
    private ?string $imdb_id;

    private bool $isFullCalled = false;

    protected array $data = [
        'imdb_id' => null,
        'main_url' => null,
        'canonical_id' => null,
        'title' => null,
        'original_title' => null,
        'type' => null,
        'year' => null,
        'end_year' => null,
        'image' => null,
        'ratings' => null,
        'rank' => null,
        'is_adult' => null,
        'production_status' => null,
        'is_ongoing' => null,
        'runtime' => null,
        'runtimes' => null,
        'genres' => null,
        'languages' => null,
        'countries' => null,
        'taglines' => null,
        'plot' => null,
        'plots' => null,
        'release_dates' => null,
        'keywords' => null,
        'locations' => null,
        'sounds' => null,
        'colors' => null,
        'aspect_ratio' => null,
        'cameras' => null,
        'certificates' => null,
        'images' => null,
        'videos' => null,
        'news' => null,
        'metacritic' => null,
        'faq' => null,
        'akas' => null,
        'alternate_versions' => null,
        'companies_production' => null,
        'companies_distribution' => null,
        'companies_special_effects' => null,
        'companies_other' => null,
    ];

    /**
     * @param string $id IMDB ID to use for data retrieval
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(string $id, Config $config = null)
    {
        parent::__construct($config);
        $this->imdb_id = $id;
        $this->data['imdb_id'] = $id;
        $this->data['main_url'] = $this->mainUrl();
    }

    /***************************************[ Methods ]***************************************/

    /**
     * Set up the URL to the title page
     *
     * @return string
     */
    public function mainUrl(): string
    {
        return $this->makeUrl("title", $this->imdb_id);
    }

    /**
     * Get imdb id
     *
     * @return string
     */
    public function imdbId(): string
    {
        return $this->imdb_id;
    }

    /**
     * This function returns the full extracted data in a single JSON-compatible array.
     *
     * @param array|null $methods
     * @return array
     * @throws Exception
     */
    public function full(?array $methods = []): array
    {
        if ($this->isFullCalled) {
            return $this->data;
        }
        $this->isFullCalled = true;

        $query = <<<GRAPHQL
query Title(\$id: ID!) {
  title(id: \$id) {
    meta {
      canonicalId
    }
    titleText {
      text
    }
    originalTitleText {
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
      width
      height
    }
    ratingsSummary {
      aggregateRating
      voteCount
      topRanking {
        rank
      }
    }
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
    }
    isAdult
    productionStatus {
      currentProductionStage {
        text
      }
    }
    episodes {
      isOngoing
    }
    runtime {
      seconds
    }
    runtimes(first: 9999) {
      edges {
        node {
          attributes {
            text
          }
          country {
            text
          }
          seconds
        }
      }
    }
    titleGenres {
      genres {
        genre {
          text
        }
      }
    }
    spokenLanguages {
      spokenLanguages {
        id
        text
      }
    }
    countriesOfOrigin {
      countries {
        id
        text
      }
    }
    taglines(first: 9999) {
      edges {
        node {
          text
        }
      }
    }
    plot {
      plotText {
        plainText
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "Title", ["id" => $this->imdb_id]);

        /***************** Parse data *****************/
        $this->parseCanonicalId($data);
        $this->titleParse($data);
        $this->originalTitleParse($data);
        $this->typeParse($data);
        $this->yearParse($data);
        $this->imageParse($data);
        $this->ratingVotesParse($data);
        $this->rankParse($data);
        $this->isAdultParse($data);
        $this->productionStatusParse($data);
        $this->isOngoingParse($data);
        $this->runtimeParse($data);
        $this->runtimesParse($data);
        $this->genresParse($data);
        $this->languagesParse($data);
        $this->countriesParse($data);
        $this->taglinesParse($data);
        $this->plotParse($data);

        if (count($methods)) {
            foreach ($methods as $method) {
                $camelCaseMethod = lcfirst(str_replace('_', '', ucwords($method, '_')));
                if (method_exists($this, $camelCaseMethod)) {
                    $this->$camelCaseMethod();
                }
            }
        }

        return $this->data;
    }

    /**
     * Check redirect to another id or not.
     *
     * @return string|null
     * @throws Exception
     */
    public function canonicalId(): ?string
    {
        if (!$this->isFullCalled) {
            $query = <<<GRAPHQL
query Redirect(\$id: ID!) {
  title(id: \$id) {
    meta {
      canonicalId
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Redirect", ["id" => $this->imdb_id]);
            $this->parseCanonicalId($data);
        }

        return $this->data['canonical_id'];
    }

    /**
     * Parse redirect
     *
     * @param $data
     * @return void
     */
    private function parseCanonicalId($data): void
    {
        if (!empty($data->title->meta->canonicalId)) {
            $canonicalId = $data->title->meta->canonicalId;
            if ($canonicalId != $this->imdb_id) {
                $this->data['canonical_id'] = $canonicalId;
            }
        }
    }

    /**
     * Setup title, year and type properties
     *
     * @return void
     * @throws Exception
     */
    private function setupTitleYearType(): void
    {
        if (!$this->isFullCalled && empty($this->data['title'])) {
            $query = <<<GRAPHQL
query Title(\$id: ID!) {
  title(id: \$id) {
    titleText {
      text
    }
    originalTitleText {
      text
    }
    titleType {
      text
    }
    releaseYear {
      year
      endYear
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Title", ["id" => $this->imdb_id]);
            $this->titleParse($data);
            $this->originalTitleParse($data);
            $this->typeParse($data);
            $this->yearParse($data);
        }
    }

    /**
     * Get movie/series title
     *
     * @return string|null
     * @throws Exception
     */
    public function title(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['title'])) {
            $this->setupTitleYearType();
        }

        return $this->data['title'];
    }

    /**
     * Parse movie/series title
     *
     * @param $data
     * @return void
     */
    private function titleParse($data): void
    {
        if (!empty($data->title->titleText->text)) {
            $this->data['title'] = $data->title->titleText->text;
        }
    }

    /**
     * Get movie/series original title
     *
     * @return string|null
     * @throws Exception
     */
    public function originalTitle(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['original_title'])) {
            $this->setupTitleYearType();
        }

        return $this->data['original_title'];
    }

    /**
     * Parse movie/series original title
     *
     * @param $data
     * @return void
     */
    private function originalTitleParse($data): void
    {
        if (!empty($data->title->originalTitleText->text)
            && $data->title->originalTitleText->text != $this->data['title']
            && $data->title->originalTitleText->text != $data->title->titleText->text) {
            $this->data['original_title'] = $data->title->originalTitleText->text;
        }
    }

    /**
     * Get title type.
     * it can be returned (Movie, TV Series, TV Episode, TV Special, TV Movie, TV Mini-Series, Video Game, TV Short, Video)
     *
     * @return string|null
     * @throws Exception
     */
    public function type(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['type'])) {
            $this->setupTitleYearType();
        }

        return $this->data['type'];
    }

    /**
     * Parse type title
     *
     * @param $data
     * @return void
     */
    private function typeParse($data): void
    {
        if (!empty($data->title->titleType->text)) {
            $this->data['type'] = $data->title->titleType->text;
        }
    }

    /**
     * Get movie/series year.
     *
     * @return int|null
     * @throws Exception
     */
    public function year(): ?int
    {
        if (!$this->isFullCalled && empty($this->data['year'])) {
            $this->setupTitleYearType();
        }

        return $this->data['year'];
    }

    /**
     * Get series end year.
     * this method return usually for series.
     *
     * @return int|null
     * @throws Exception
     */
    public function endYear(): ?int
    {
        if (!$this->isFullCalled && empty($this->data['end_year'])) {
            $this->setupTitleYearType();
        }

        return $this->data['end_year'];
    }

    /**
     * Parse year and end year
     *
     * @param $data
     * @return void
     */
    private function yearParse($data): void
    {
        if (!empty($data->title->releaseYear->year)) {
            $this->data['year'] = $data->title->releaseYear->year;
        }
        if (!empty($data->title->releaseYear->endYear)) {
            $this->data['end_year'] = $data->title->releaseYear->endYear;
        }
    }

    /**
     * Get image
     *
     * @return array|null
     * @throws Exception
     */
    public function image(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['image'])) {
            $query = <<<GRAPHQL
query Image(\$id: ID!) {
  title(id: \$id) {
    primaryImage {
      url
      width
      height
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Image", ["id" => $this->imdb_id]);
            $this->imageParse($data);
        }

        return $this->data['image'];
    }

    /**
     * Parse image
     *
     * @param $data
     * @return void
     */
    private function imageParse($data): void
    {
        if (!empty($data->title->primaryImage->url)) {
            $this->data['image'] = $this->parseImage($data->title->primaryImage);
        }
    }

    /**
     * Get rating and votes
     *
     * @return array|null
     * @throws Exception
     */
    private function ratings(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['ratings'])) {
            $query = <<<GRAPHQL
query RatingVotes(\$id: ID!) {
  title(id: \$id) {
    ratingsSummary {
      aggregateRating
      voteCount
      topRanking {
        rank
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "RatingVotes", ["id" => $this->imdb_id]);
            $this->ratingVotesParse($data);
        }

        return $this->data['ratings'];
    }

    /**
     * Parse Rating and Votes
     *
     * @param $data
     * @return void
     */
    private function ratingVotesParse($data): void
    {
        $this->data['ratings']['rating'] = $data->title->ratingsSummary->aggregateRating ?? null;
        $this->data['ratings']['votes'] = $data->title->ratingsSummary->voteCount ?? null;
        $this->data['ratings']['rank_in_top250'] = $data->title->ratingsSummary->topRanking->rank <= 250 ? $data->title->ratingsSummary->topRanking->rank : null;
    }

    /**
     * Get title popularity rank
     *
     * @return array|null
     * @throws Exception
     */
    public function rank(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['rank'])) {
            $query = <<<GRAPHQL
query Rank(\$id: ID!) {
  title(id: \$id) {
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Rank", ["id" => $this->imdb_id]);
            $this->rankParse($data);
        }

        return $this->data['rank'];
    }

    /**
     * Parse rank
     *
     * @param $data
     * @return void
     */
    private function rankParse($data): void
    {
        if (!empty($data->title->meterRanking)) {
            $this->data['rank']['current_rank'] = $data->title->meterRanking->currentRank ?? null;
            $this->data['rank']['change_direction'] = $data->title->meterRanking->rankChange->changeDirection ?? null;
            $this->data['rank']['difference'] = $data->title->meterRanking->rankChange->difference ?? null;
        }
    }

    /**
     * Get adult status of a title
     *
     * @return bool|null
     * @throws Exception
     */
    public function isAdult(): ?bool
    {
        if (!$this->isFullCalled && empty($this->data['is_adult'])) {
            $query = <<<GRAPHQL
query Adult(\$id: ID!) {
  title(id: \$id) {
    isAdult
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Adult", ["id" => $this->imdb_id]);
            $this->isAdultParse($data);
        }

        return $this->data['is_adult'];
    }

    /**
     * Parse adult status
     *
     * @param $data
     * @return void
     */
    private function isAdultParse($data): void
    {
        if (isset($data->title->isAdult)) {
            $this->data['is_adult'] = $data->title->isAdult;
        }
    }

    /**
     * Get current production status of a title e.g. Released, In Development, Pre-Production, Complete, Production etc
     *
     * @return string|null
     * @throws Exception
     */
    public function productionStatus(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['production_status'])) {
            $query = <<<GRAPHQL
query ProductionStatus(\$id: ID!) {
  title(id: \$id) {
    productionStatus {
      currentProductionStage {
        text
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "ProductionStatus", ["id" => $this->imdb_id]);
            $this->productionStatusParse($data);
        }

        return $this->data['production_status'];
    }

    /**
     * Parse production status
     *
     * @param $data
     * @return void
     */
    private function productionStatusParse($data): void
    {
        if (isset($data->title->productionStatus->currentProductionStage->text)) {
            $this->data['production_status'] = $data->title->productionStatus->currentProductionStage->text;
        }
    }

    /**
     * IsOngoing TV Series
     * false if ended, true if still running or null (not a tv series)
     *
     * @return bool|null
     * @throws Exception
     */
    public function isOngoing(): ?bool
    {
        if (!$this->isFullCalled && empty($this->data['is_ongoing'])) {
            $query = <<<GRAPHQL
query IsOngoing(\$id: ID!) {
  title(id: \$id) {
    episodes {
      isOngoing
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "IsOngoing", ["id" => $this->imdb_id]);
            $this->isOngoingParse($data);
        }

        return $this->data['is_ongoing'];
    }

    /**
     * Parse isOngoing status for TV Series
     *
     * @param $data
     * @return void
     */
    private function isOngoingParse($data): void
    {
        if (isset($data->title->episodes->isOngoing)) {
            $this->data['is_ongoing'] = $data->title->episodes->isOngoing;
        }
    }

    /**
     * Get title main runtime
     *
     * @return int|null
     * @throws Exception
     */
    public function runtime(): ?int
    {
        if (!$this->isFullCalled && empty($this->data['runtime'])) {
            $query = <<<GRAPHQL
query Runtime(\$id: ID!) {
  title(id: \$id) {
    runtime {
      seconds
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Runtime", ["id" => $this->imdb_id]);
            $this->runtimeParse($data);
        }

        return $this->data['runtime'];
    }

    /**
     * Parse runtime
     *
     * @param $data
     * @return void
     */
    private function runtimeParse($data): void
    {
        if (!empty($data->title->runtime)) {
            $this->data['runtime'] = $data->title->runtime->seconds / 60;
        }
    }

    /**
     * Retrieve all runtimes and their descriptions
     *
     * @return array|null
     * @throws Exception
     */
    public function runtimes(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['runtimes'])) {
            $query = <<<GRAPHQL
query Runtimes(\$id: ID!) {
  title(id: \$id) {
    runtimes(first: 9999) {
      edges {
        node {
          attributes {
            text
          }
          country {
            text
          }
          seconds
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Runtimes", ["id" => $this->imdb_id]);
            $this->runtimesParse($data);
        }

        return $this->data['runtimes'];
    }

    /**
     * Parse runtimes
     *
     * @param $data
     * @return void
     */
    private function runtimesParse($data): void
    {
        if ($this->hasArrayItems($data->title->runtimes->edges)) {
            foreach ($data->title->runtimes->edges as $edge) {
                $attributes = [];
                if ($this->hasArrayItems($edge->node->attributes)) {
                    foreach ($edge->node->attributes as $attribute) {
                        if (!empty($attribute->text)) {
                            $attributes[] = $attribute->text;
                        }
                    }
                }

                $this->data['runtimes'][] = [
                    'time' => isset($edge->node->seconds) ? $edge->node->seconds / 60 : null,
                    'annotations' => $attributes,
                    'country' => $edge->node->country->text ?? null
                ];
            }
        }
    }

    /**
     * Get all genres the movie is registered for
     *
     * @return array|null
     * @throws Exception
     */
    public function genres(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['genres'])) {
            $query = <<<GRAPHQL
query Genres(\$id: ID!) {
  title(id: \$id) {
    titleGenres {
      genres {
        genre {
          text
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Genres", ["id" => $this->imdb_id]);
            $this->genresParse($data);
        }

        return $this->data['genres'];
    }

    /**
     * Parse genres
     *
     * @param $data
     * @return void
     */
    private function genresParse($data): void
    {
        if ($this->hasArrayItems($data->title->titleGenres->genres)) {
            foreach ($data->title->titleGenres->genres as $edge) {
                $this->data['genres'][] = $edge->genre->text;
            }
        }
    }

    /**
     * Get all spoken languages spoken in this title
     *
     * @return array|null
     * @throws Exception
     */
    public function languages(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['languages'])) {
            $query = <<<GRAPHQL
query Languages(\$id: ID!) {
  title(id: \$id) {
    spokenLanguages {
      spokenLanguages {
        id
        text
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Languages", ["id" => $this->imdb_id]);
            $this->languagesParse($data);
        }

        return $this->data['languages'];
    }

    /**
     * Parse languages
     *
     * @param $data
     * @return void
     */
    private function languagesParse($data): void
    {
        if ($this->hasArrayItems($data->title->spokenLanguages->spokenLanguages)) {
            foreach ($data->title->spokenLanguages->spokenLanguages as $language) {
                if (!empty($language->text)) {
                    $this->data['languages'][] = [
                        'id' => $language->id,
                        'name' => $language->text
                    ];
                }
            }
        }
    }

    /**
     * Get country of production
     *
     * @return array|null
     * @throws Exception
     */
    public function countries(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['countries'])) {
            $query = <<<GRAPHQL
query Countries(\$id: ID!) {
  title(id: \$id) {
    countriesOfOrigin {
      countries {
        id
        text
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Countries", ["id" => $this->imdb_id]);
            $this->countriesParse($data);
        }

        return $this->data['countries'];
    }

    /**
     * Parse countries
     *
     * @param $data
     * @return void
     */
    private function countriesParse($data): void
    {
        if ($this->hasArrayItems($data->title->countriesOfOrigin->countries)) {
            foreach ($data->title->countriesOfOrigin->countries as $country) {
                if (!empty($country->text)) {
                    $this->data['countries'][] = [
                        'id' => $country->id,
                        'name' => $country->text
                    ];
                }
            }
        }
    }

    /**
     * Get all available taglines for the title
     *
     * @return array|null
     * @throws Exception
     */
    public function taglines(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['taglines'])) {
            $query = <<<GRAPHQL
query Taglines(\$id: ID!) {
  title(id: \$id) {
    taglines(first: 9999) {
      edges {
        node {
          text
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Taglines", ["id" => $this->imdb_id]);
            $this->taglinesParse($data);
        }

        return $this->data['taglines'];
    }

    /**
     * Parse taglines
     *
     * @param $data
     * @return void
     */
    private function taglinesParse($data): void
    {
        if ($this->hasArrayItems($data->title->taglines->edges)) {
            foreach ($data->title->taglines->edges as $edge) {
                $this->data['taglines'][] = $edge->node->text;
            }
        }
    }

    /**
     * Get the main Plot outline for the movie as displayed on top of title page
     *
     * @return string|null
     * @throws Exception
     */
    public function plot(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['plot'])) {
            $query = <<<GRAPHQL
query PlotOutline(\$id: ID!) {
  title(id: \$id) {
    plot {
      plotText {
        plainText
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "PlotOutline", ["id" => $this->imdb_id]);
            $this->plotParse($data);
        }

        return $this->data['plot'];
    }

    /**
     * Parse main plot
     *
     * @param $data
     * @return void
     */
    private function plotParse($data): void
    {
        if (!empty($data->title->plot->plotText->plainText)) {
            $this->data['plot'] = $data->title->plot->plotText->plainText;
        }
    }

    /**
     * Get all plots
     *
     * @param bool $spoil
     * @return array|null
     * @throws Exception
     */
    public function plots(bool $spoil = false): ?array
    {
        if (empty($this->data['plots'])) {
            $filter = $spoil === false ? ',filter:{spoilers:EXCLUDE_SPOILERS}' : '';
            $query = <<<GRAPHQL
query Plots(\$id: ID!) {
  title(id: \$id) {
    plots(first: 9999$filter) {
      edges {
        node {
          author
          plotText {
            plainText
          }
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Plots", ["id" => $this->imdb_id]);
            if ($this->hasArrayItems($data->title->plots->edges)) {
                foreach ($data->title->plots->edges as $edge) {
                    if (!empty($edge->node->plotText->plainText)) {
                        $this->data['plots'][] = [
                            'plot' => $edge->node->plotText->plainText,
                            'author' => $edge->node->author ?? null
                        ];
                    }
                }
            }
        }

        return $this->data['plots'];
    }

    /**
     * Get all keywords
     *
     * @return array|null
     * @throws Exception
     */
    public function keywords(): ?array
    {
        if (empty($this->data['keywords'])) {
            $query = <<<GRAPHQL
keyword {
  text {
    text
  }
}
GRAPHQL;

            $data = $this->getAllData("Keywords", "keywords", $query);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    if (!empty($edge->node->keyword->text->text)) {
                        $this->data['keywords'][] = $edge->node->keyword->text->text;
                    }
                }
            }
        }

        return $this->data['keywords'];
    }

    /**
     * Get all release dates for this title
     *
     * @return array|null
     * @throws Exception
     */
    public function releaseDates(): ?array
    {
        if (empty($this->data['release_dates'])) {
            $query = <<<GRAPHQL
country {
  text
}
day
month
year
attributes {
  text
}
GRAPHQL;

            $data = $this->getAllData("ReleaseDates", "releaseDates", $query);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    $attributes = [];
                    if ($this->hasArrayItems($edge->node->attributes)) {
                        foreach ($edge->node->attributes as $attribute) {
                            if (!empty($attribute->text)) {
                                $attributes[] = $attribute->text;
                            }
                        }
                    }

                    $releaseDate = $this->buildDate($edge->node->day ?? null, $edge->node->month ?? null, $edge->node->year ?? null);

                    $this->data['release_dates'][] = [
                        'country' => $edge->node->country->text ?? null,
                        'release_date' => $releaseDate,
                        'day' => $edge->node->day ?? null,
                        'month' => $edge->node->month ?? null,
                        'year' => $edge->node->year ?? null,
                        'attributes' => $attributes
                    ];
                }
            }
        }

        return $this->data['release_dates'];
    }

    /**
     * Get all Filming locations
     *
     * @return array|null
     * @throws Exception
     */
    public function locations(): ?array
    {
        if (empty($this->data['locations'])) {
            $query = <<<GRAPHQL
displayableProperty {
  qualifiersInMarkdownList {
    plainText
  }
  value {
    plainText
  }
}
GRAPHQL;

            $data = $this->getAllData("FilmingLocations", "filmingLocations", $query);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    $scenes = null;
                    if ($this->hasArrayItems($edge->node->displayableProperty->qualifiersInMarkdownList)) {
                        foreach ($edge->node->displayableProperty->qualifiersInMarkdownList as $attribute) {
                            if (!empty($attribute->plainText)) {
                                $scenes[] = $attribute->plainText;
                            }
                        }
                    }

                    $this->data['locations'][] = [
                        'real' => $edge->node->displayableProperty->value->plainText ?? null,
                        'scenes' => $scenes
                    ];
                }
            }
        }

        return $this->data['locations'];
    }

    /**
     * Get movie sound mixes
     *
     * @return array|null
     * @throws Exception
     */
    public function sounds(): ?array
    {
        if (empty($this->data['sounds'])) {
            $this->techSpec("soundMixes", "text", 'sounds');
        }

        return $this->data['sounds'];
    }

    /**
     * Get movie colorations like color or Black and white
     *
     * @return array|null
     * @throws Exception
     */
    public function colors(): ?array
    {
        if (empty($this->data['colors'])) {
            $this->techSpec("colorations", "text", 'colors');
        }

        return $this->data['colors'];
    }

    /**
     * Get movie aspect ratio like 1.66:1 or 16:9
     *
     * @return array|null
     * @throws Exception
     */
    public function aspectRatio(): ?array
    {
        if (empty($this->data['aspect_ratio'])) {
            $this->techSpec("aspectRatios", "aspectRatio", 'aspect_ratio');
        }

        return $this->data['aspect_ratio'];
    }

    /**
     * Get cameras used in this title
     *
     * @return array|null
     * @throws Exception
     */
    public function cameras(): ?array
    {
        if (empty($this->data['cameras'])) {
            $this->techSpec("cameras", "camera", 'cameras');
        }

        return $this->data['cameras'];
    }

    /**
     * Get all certificates / Parental Guidance / Age rating for this title by country
     *
     * @return array|null
     * @throws Exception
     */
    public function certificates(): ?array
    {
        if (empty($this->data['certificates'])) {
            $query = <<<GRAPHQL
country {
  text
}
rating
attributes {
  text
}
GRAPHQL;

            $data = $this->getAllData("Mpaa", "certificates", $query);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    $comments = null;
                    if ($this->hasArrayItems($edge->node->attributes)) {
                        foreach ($edge->node->attributes as $attribute) {
                            if (!empty($attribute->text)) {
                                $comments[] = $attribute->text;
                            }
                        }
                    }

                    $this->data['certificates'][] = [
                        'country' => $edge->node->country->text ?? null,
                        'rating' => $edge->node->rating ?? null,
                        'comment' => $comments
                    ];
                }
            }
        }

        return $this->data['certificates'];
    }

    /**
     * Get all images
     *
     * @param int $limit
     * @return array|null
     * @throws Exception
     */
    public function images(int $limit = 9999): ?array
    {
        if (empty($this->data['images'])) {
            $query = <<<GRAPHQL
query Images(\$id: ID!) {
  title(id: \$id) {
    images(first: $limit) {
      edges {
        node {
          id
          url
          width
          height
          caption {
            plainText
          }
          copyright
          titles {
            id
            titleText {
              text
            }
          }
          names {
            id
            nameText {
              text
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Images", ["id" => $this->imdb_id]);
            $this->imagesParse($data);
        }

        return $this->data['images'];
    }

    /**
     * Parse images
     *
     * @param $data
     * @return void
     */
    private function imagesParse($data): void
    {
        if ($this->hasArrayItems($data->title->images->edges)) {
            $images = [];
            foreach ($data->title->images->edges as $edge) {
                if (empty($edge->node->id) || empty($edge->node->url)) {
                    continue;
                }

                // Titles
                $titles = [];
                if (!empty($edge->node->titles)) {
                    foreach ($edge->node->titles as $title) {
                        if (empty($title->id) || empty($title->titleText->text)) {
                            continue;
                        }

                        $titles[] = [
                            'id' => $title->id,
                            'title' => $title->titleText->text
                        ];
                    }
                }

                // Names
                $names = [];
                if (!empty($edge->node->names)) {
                    foreach ($edge->node->names as $name) {
                        if (empty($name->id) || empty($name->nameText->text)) {
                            continue;
                        }

                        $names[] = [
                            'id' => $name->id,
                            'name' => $name->nameText->text
                        ];
                    }
                }

                $images[] = [
                    'id' => $edge->node->id,
                    'caption' => $edge->node->caption->plainText ?? null,
                    'copyright' => $edge->node->copyright ?? null,
                    'image' => $this->parseImage($edge->node),
                    'titles' => $titles,
                    'names' => $names,
                ];
            }

            $this->data['images'] = $images;
        }
    }

    /**
     * Get all videos
     *
     * @param int $limit
     * @param string|null $videoContentType
     * @param bool $videoIncludeMature
     * @return array|null
     * @throws Exception
     */
    public function videos(int $limit = 9999, string $videoContentType = null, bool $videoIncludeMature = false): ?array
    {
        if (empty($this->data['videos'])) {
            $filter = $videoIncludeMature === true ? ',filter:{maturityLevel:INCLUDE_MATURE}' : '';
            $query = <<<GRAPHQL
query Video(\$id: ID!) {
  title(id: \$id) {
    videoStrip(first:$limit $filter) {
      edges {
        node {
          id
          name {
            value
          }
          runtime {
            value
          }
          videoDimensions {
            aspectRatio
          }
          contentType {
            displayName {
              value
            }
          }
          description {
            value
          }
          thumbnail {
            url
            width
            height
          }
          createdDate
          isMature
          primaryTitle {
            id
            titleText {
              text
            }
            releaseDate {
              day
              month
              year
              displayableProperty {
                value {
                  plainText
                }
              }
            }
            releaseYear {
              year
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
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Video", ["id" => $this->imdb_id]);

            if ($this->hasArrayItems($data->title->videoStrip->edges)) {
                $videoClass = new Video();
                $this->data['videos'] = $videoClass->parseVideoResults($data->title->videoStrip->edges, $videoContentType, $videoIncludeMature);
            }
        }

        return $this->data['videos'];
    }

    /**
     * Get news items about this title, max 100 items!
     *
     * @param int $limit
     * @return array|null
     * @throws Exception
     */
    public function news(int $limit = 100): ?array
    {
        if (empty($this->data['news'])) {
            $query = <<<GRAPHQL
query News(\$id: ID!) {
  title(id: \$id) {
    news(first: $limit) {
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
}
GRAPHQL;
            $data = $this->graphql->query($query, "News", ["id" => $this->imdb_id]);
            if ($this->hasArrayItems($data->title->news->edges)) {
                $newsClass = new News();
                $this->data['news'] = $newsClass->parseNewsResults($data->title->news->edges);
            }
        }

        return $this->data['news'];
    }

    /**
     * Metacritic data like score and reviews
     *
     * @return array|null
     * @throws Exception
     */
    public function metacritic(): ?array
    {
        if (empty($this->data['metacritic'])) {
            $query = <<<GRAPHQL
query Metacritic(\$id: ID!) {
  title(id: \$id) {
    metacritic {
      url
      metascore {
        score
        reviewCount
      }
      reviews(first:9999) {
        edges {
          node {
            reviewer
            score
            site
            url
            quote {
              value
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Metacritic", ["id" => $this->imdb_id]);

            $reviews = [];
            if (isset($data->title->metacritic->reviews) && $this->hasArrayItems($data->title->metacritic->reviews->edges)) {
                foreach ($data->title->metacritic->reviews->edges as $edge) {
                    $reviews[] = [
                        'reviewer' => $edge->node->reviewer ?? null,
                        'score' => $edge->node->score ?? 0,
                        'quote' => $edge->node->quote->value ?? null,
                        'site_name' => $edge->node->site ?? null,
                        'site_url' => $edge->node->url ?? null
                    ];
                }
            }

            $this->data['metacritic'] = [
                'url' => $data->title->metacritic->url ?? null,
                'score' => $data->title->metacritic->metascore->score ?? 0,
                'review_count' => $data->title->metacritic->metascore->reviewCount ?? 0,
                'reviews' => $reviews
            ];
        }

        return $this->data['metacritic'];
    }

    /**
     * Get movie frequently asked questions, it includes questions with and without answer
     *
     * @param bool $spoil (true or false) to include spoilers or not, isSpoiler indicates if this question is spoiler or not
     * @return array|null
     * @throws Exception
     */
    public function faq(bool $spoil = false): ?array
    {
        if (empty($this->data['faq'])) {
            $filter = $spoil === false ? ', filter: {spoilers: EXCLUDE_SPOILERS}' : '';
            $query = <<<GRAPHQL
question {
  plainText
}
answer {
  plainText
}
isSpoiler
GRAPHQL;

            $data = $this->getAllData("Faq", "faqs", $query, $filter);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    $this->data['faq'][] = [
                        'question' => $edge->node->question->plainText ?? null,
                        'answer' => $edge->node->answer->plainText ?? null,
                        'is_spoiler' => $edge->node->isSpoiler
                    ];
                }
            }
        }

        return $this->data['faq'];
    }

    /**
     * Get title's alternative names
     *
     * @return array|null
     * @throws Exception
     */
    public function akas(): ?array
    {
        if (empty($this->data['akas'])) {
            $filter = ', sort: {order: ASC by: COUNTRY}';
            $query = <<<GRAPHQL
country {
  id
  text
}
text
attributes {
  text
}
language {
  id
  text
}
GRAPHQL;

            $data = $this->getAllData("AlsoKnow", "akas", $query, $filter);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    $comments = [];
                    if ($this->hasArrayItems($edge->node->attributes)) {
                        foreach ($edge->node->attributes as $attribute) {
                            if (!empty($attribute->text)) {
                                $comments[] = $attribute->text;
                            }
                        }
                    }

                    $this->data['akas'][] = [
                        'title' => $edge->node->text ?? null,
                        'country' => isset($edge->node->country->text) ? ucwords($edge->node->country->text) : 'Unknown',
                        'country_id' => $edge->node->country->id ?? null,
                        'language' => isset($edge->node->language->text) ? ucwords($edge->node->language->text) : null,
                        'language_id' => $edge->node->language->id ?? null,
                        'comments' => $comments
                    ];
                }
            }
        }

        return $this->data['akas'];
    }

    /**
     * Get the Alternate Versions for a given title
     *
     * @return array|null
     * @throws Exception
     */
    public function alternateVersions(): ?array
    {
        if (empty($this->data['alternate_versions'])) {
            $query = <<<GRAPHQL
text {
  plainText
}
GRAPHQL;

            $data = $this->getAllData("AlternateVersions", "alternateVersions", $query);
            if ($this->hasArrayItems($data)) {
                foreach ($data as $edge) {
                    if (!empty($edge->node->text->plainText)) {
                        $this->data['alternate_versions'][] = $edge->node->text->plainText;
                    }
                }
            }
        }

        return $this->data['alternate_versions'];
    }

    /**
     * Info about Production Companies
     *
     * @return array|null
     * @throws Exception
     */
    public function companiesProduction(): ?array
    {
        if (empty($this->data['companies_production'])) {
            $this->data['companies_production'] = $this->companyCredits("production");
        }

        return $this->data['companies_production'];
    }

    /**
     * Info about distributors
     *
     * @return array|null
     * @throws Exception
     */
    public function companiesDistribution(): ?array
    {
        if (empty($this->data['companies_distribution'])) {
            $this->data['companies_distribution'] = $this->companyCredits("distribution");
        }
        return $this->data['companies_distribution'];
    }

    /**
     * Info about Special Effects companies
     *
     * @return array|null
     * @throws Exception
     */
    public function companiesSpecialEffects(): ?array
    {
        if (empty($this->data['companies_special_effects'])) {
            $this->data['companies_special_effects'] = $this->companyCredits("specialEffects");
        }

        return $this->data['companies_special_effects'];
    }

    /**
     * Info about other companies
     *
     * @return array|null
     * @throws Exception
     */
    public function companiesOther(): ?array
    {
        if (empty($this->data['companies_other'])) {
            $this->data['companies_other'] = $this->companyCredits("miscellaneous");
        }
        return $this->data['companies_other'];
    }

    /***************************************[ Helper Methods ]***************************************/
    /**
     * Get all edges of a field in the title type
     *
     * @param string $queryName
     * @param string $fieldName
     * @param string $nodeQuery
     * @param string $filter
     * @return array
     * @throws Exception
     */
    protected function getAllData(string $queryName, string $fieldName, string $nodeQuery, string $filter = ""): array
    {
        $query = <<<EOF
query $queryName(\$id: ID!, \$after: ID) {
  title(id: \$id) {
    $fieldName(first: 9999, after: \$after$filter) {
      edges {
        node {
          $nodeQuery
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
}
EOF;
        $fullQuery = implode("\n", array_map('trim', explode("\n", $query)));

        // Results are paginated, so loop until we've got all the data
        $endCursor = null;
        $hasNextPage = true;
        $edges = [];
        while ($hasNextPage) {
            $data = $this->graphql->query($fullQuery, $queryName, ["id" => $this->imdb_id, "after" => $endCursor]);
            if (isset($data->title->{$fieldName})) {
                $edges = array_merge($edges, $data->title->{$fieldName}->edges);
                $hasNextPage = $data->title->{$fieldName}->pageInfo->hasNextPage;
                $endCursor = $data->title->{$fieldName}->pageInfo->endCursor;
            }
        }

        return $edges;
    }

    /**
     * Get movie tech specs
     *
     * @throws Exception
     */
    protected function techSpec(string $type, string $valueType, string $arrayName): void
    {
        $query = <<<GRAPHQL
query TechSpec(\$id: ID!) {
  title(id: \$id) {
    technicalSpecifications {
      $type {
        items {
          $valueType
          attributes {
            text
          }
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "TechSpec", ["id" => $this->imdb_id]);

        if ($this->hasArrayItems($data->title->technicalSpecifications->$type->items)) {
            foreach ($data->title->technicalSpecifications->$type->items as $item) {
                $attributes = null;
                if ($this->hasArrayItems($item->attributes)) {
                    foreach ($item->attributes as $attribute) {
                        if (!empty($attribute->text)) {
                            $attributes[] = $attribute->text;
                        }
                    }
                }

                $this->data[$arrayName][] = [
                    'value' => $item->$valueType ?? null,
                    'attributes' => $attributes
                ];
            }
        }
    }

    /**
     * Fetch all company credits
     *
     * @param string $category e.g. distribution, production
     * @return array
     * @throws Exception
     */
    protected function companyCredits(string $category): array
    {
        $filter = ', filter: { categories: ["' . $category . '"] }';
        $query = <<<EOF
company {
  id
}
displayableProperty {
  value {
    plainText
  }
}
countries(limit: 1) {
  text
}
attributes {
  text
}
yearsInvolved {
  year
}
EOF;
        $data = $this->getAllData("CompanyCredits", "companyCredits", $query, $filter);
        $results = [];
        if ($this->hasArrayItems($data)) {
            foreach ($data as $edge) {
                $companyAttribute = [];
                if ($this->hasArrayItems($edge->node->attributes)) {
                    foreach ($edge->node->attributes as $attribute) {
                        $companyAttribute[] = $attribute->text;
                    }
                }

                $results[] = [
                    "id" => $edge->node->company->id ?? null,
                    "name" => $edge->node->displayableProperty->value->plainText ?? null,
                    "country" => $edge->node->countries[0]->text ?? null,
                    "attribute" => $companyAttribute,
                    "year" => $edge->node->yearsInvolved->year ?? null,
                ];
            }
        }

        return $results;
    }
}
