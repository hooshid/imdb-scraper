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
        'genres' => null,
        'languages' => null,
        'countries' => null,
        'runtime' => null,
        'runtimes' => null,
        'taglines' => null,
        'keywords' => null,
        'locations' => null,
        'sounds' => null,
        'colors' => null,
        'aspect_ratio' => null,
        'cameras' => null,
        'mpaas' => null,
        'videos' => null,
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
    }
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
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
        subGenres {
          keyword {
            text {
              text
            }
          }
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
        $this->runtimeParse($data);
        $this->runtimesParse($data);
        $this->genresParse($data);
        $this->languagesParse($data);
        $this->countriesParse($data);
        $this->taglinesParse($data);

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
     * Parse redirects
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
     * Get movie/series type.
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
     * Parse rank
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
        if (!empty($data->title->runtimes->edges)) {
            foreach ($data->title->runtimes->edges as $edge) {
                $attributes = [];
                if (!empty($edge->node->attributes)) {
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
        if (!empty($data->title->spokenLanguages->spokenLanguages)) {
            foreach ($data->title->spokenLanguages->spokenLanguages as $language) {
                if (!empty($language->text)) {
                    $this->data['languages'][] = [
                        'code' => $language->id,
                        'name' => $language->text
                    ];
                }
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
        subGenres {
          keyword {
            text {
              text
            }
          }
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
        if (!empty($data->title->titleGenres->genres)) {
            foreach ($data->title->titleGenres->genres as $edge) {
                $subGenres = null;
                if (isset($edge->subGenres) && is_array($edge->subGenres) && count($edge->subGenres) > 0) {
                    foreach ($edge->subGenres as $subGenre) {
                        if (!empty($subGenre->keyword->text->text)) {
                            $subGenres[] = ucwords($subGenre->keyword->text->text);
                        }
                    }
                }

                $this->data['genres'][] = [
                    'genre' => $edge->genre->text ?? null,
                    'subs' => $subGenres
                ];
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
        if (!empty($data->title->countriesOfOrigin->countries)) {
            foreach ($data->title->countriesOfOrigin->countries as $country) {
                if (!empty($country->text)) {
                    $this->data['countries'][] = [
                        'code' => $country->id,
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
        if (!empty($data->title->taglines->edges)) {
            foreach ($data->title->taglines->edges as $edge) {
                $this->data['taglines'][] = $edge->node->text;
            }
        }
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
            if (count($data) > 0) {
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
            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $scenes = null;
                    if (isset($edge->node->displayableProperty->qualifiersInMarkdownList)) {
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
     * Get the MPAA rating / Parental Guidance / Age rating for this title by country
     *
     * @return array|null
     * @throws Exception
     */
    public function mpaas(): ?array
    {
        if (empty($this->data['mpaas'])) {
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
            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $comments = null;
                    if (isset($edge->node->attributes)) {
                        foreach ($edge->node->attributes as $attribute) {
                            if (!empty($attribute->text)) {
                                $comments[] = $attribute->text;
                            }
                        }
                    }

                    $this->data['mpaas'][] = [
                        'country' => $edge->node->country->text ?? null,
                        'rating' => $edge->node->rating ?? null,
                        'comment' => $comments
                    ];
                }
            }
        }

        return $this->data['mpaas'];
    }

    /**
     * Get all available taglines for the title
     *
     * @return array|null
     * @throws Exception
     */
    public function videos(string $videoContentType = null, bool $videoIncludeMature = false): ?array
    {
        if (empty($this->data['videos'])) {
            $filter = $videoIncludeMature === true ? ',filter:{maturityLevel:INCLUDE_MATURE}' : '';
            $query = <<<GRAPHQL
query Video(\$id: ID!) {
  title(id: \$id) {
    videoStrip(first:9999$filter) {
      edges {
        node {
          id
          name {
            value
          }
          runtime {
            value
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
          primaryTitle {
            titleText {
              text
            }
            releaseYear {
              year
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

            $data = $this->graphql->query($query, "Video", ["id" => $this->imdb_id]);

            if (isset($data->title->videoStrip->edges) &&
                is_array($data->title->videoStrip->edges) &&
                count($data->title->videoStrip->edges) > 0
            ) {
                foreach ($data->title->videoStrip->edges as $edge) {
                    if (!empty($videoContentType) &&
                        isset($edge->node->contentType->displayName->value) &&
                        $edge->node->contentType->displayName->value !== $videoContentType
                    ) {
                        continue;
                    }

                    $this->data['videos'][] = [
                        'id' => $edge->node->id,
                        'type' => $edge->node->contentType->displayName->value ?? null,
                        'title' => $edge->node->name->value ?? null,
                        'runtime' => $edge->node->runtime->value ?? null,
                        'description' => $edge->node->description->value ?? null,
                        'titleName' => $edge->node->primaryTitle->titleText->text ?? null,
                        'titleYear' => $edge->node->primaryTitle->releaseYear->year ?? null,
                        'playbackUrl' => !empty($videoId) ? 'https://www.imdb.com/video/vi' . $edge->node->id . '/' : null,
                        'image' => $this->parseImage($edge->node->thumbnail)
                    ];
                }
            }
        }

        return $this->data['videos'];
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

        if (isset($data->title->technicalSpecifications->$type->items)) {
            foreach ($data->title->technicalSpecifications->$type->items as $item) {
                $attributes = null;
                if (isset($item->attributes)) {
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

}
