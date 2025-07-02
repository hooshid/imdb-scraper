<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Name extends Base
{
    private ?string $imdb_id;

    private bool $isFullCalled = false;

    protected array $data = [
        'imdb_id' => null,
        'main_url' => null,
        'canonical_id' => null,
        'full_name' => null,
        'image' => null,
        'rank' => null,
        'age' => null,
        'birth' => null,
        'death' => null,
        'birth_name' => null,
        'nick_names' => null,
        'aka_names' => null,
        'body_height' => null,
        'bio' => null,
        'professions' => null,
        'spouses' => null,
        'children' => null,
        'parents' => null,
        'relatives' => null,
        'trivia' => null,
        'quotes' => null,
        'trademarks' => null,
        'salaries' => null,
        'images' => null,
        'videos' => null,
        'news' => null,
        'credit_known_for' => null,
        'credits' => null,
        'awards' => null,
        'pub_prints' => null,
        'pub_movies' => null,
        'pub_portrayal' => null,
        'pub_article' => null,
        'pub_interview' => null,
        'pub_magazine' => null,
        'pub_pictorial' => null,
        'other_works' => null,
        'external_sites' => null,
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
     * Set up the URL to the person page
     *
     * @return string
     */
    public function mainUrl(): string
    {
        return $this->makeUrl("name", $this->imdb_id);
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
query Name(\$id: ID!) {
  name(id: \$id) {
    meta {
      canonicalId
    }
    nameText {
      text
    }
    primaryImage {
      url
      width
      height
    }
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
    }
    age {
      value
    }
    birthDate {
      dateComponents {
        day
        month
        year
      }
    }
    birthLocation {
      text
    }
    deathDate {
      dateComponents {
        day
        month
        year
      }
    }
    deathLocation {
      text
    }
    deathCause {
      text
    }
    birthName {
      text
    }
    nickNames {
      text
    }
    akas(first: 9999) {
      edges {
        node {
          text
        }
      }
    }
    height {
      displayableProperty {
        value {
          plainText
        }
      }
    }
    bios(first: 9999) {
      edges {
        node {
          text {
            plainText
          }
          author {
            plainText
          }
        }
      }
    }
    primaryProfessions {
      category {
        text
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "Name", ["id" => $this->imdb_id]);

        /***************** Parse data *****************/
        $this->parseCanonicalId($data);
        $this->fullNameParse($data);
        $this->imageParse($data);
        $this->rankParse($data);
        $this->ageParse($data);
        $this->birthParse($data);
        $this->deathParse($data);
        $this->birthNameParse($data);
        $this->nickNamesParse($data);
        $this->akaNamesParse($data);
        $this->bodyHeightParse($data);
        $this->bioParse($data);
        $this->professionsParse($data);

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
  name(id: \$id) {
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
        if (!empty($data->name->meta->canonicalId)) {
            $canonicalId = $data->name->meta->canonicalId;
            if ($canonicalId != $this->imdb_id) {
                $this->data['canonical_id'] = $canonicalId;
            }
        }
    }

    /**
     * Get the name of the person
     *
     * @return string|null
     * @throws Exception
     */
    public function fullName(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['full_name'])) {
            $query = <<<GRAPHQL
query Name(\$id: ID!) {
  name(id: \$id) {
    nameText {
      text
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Name", ["id" => $this->imdb_id]);
            $this->fullNameParse($data);
        }

        return $this->data['full_name'];
    }

    /**
     * Parse full name
     *
     * @param $data
     * @return void
     */
    private function fullNameParse($data): void
    {
        if (!empty($data->name->nameText->text)) {
            $this->data['full_name'] = $data->name->nameText->text;
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
  name(id: \$id) {
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
        if (!empty($data->name->primaryImage->url)) {
            $this->data['image'] = $this->parseImage($data->name->primaryImage);
        }
    }

    /**
     * Get current popularity rank of a person
     *
     * @return array|null
     * @throws Exception
     */
    public function rank(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['rank'])) {
            $query = <<<GRAPHQL
query Rank(\$id: ID!) {
  name(id: \$id) {
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
        if (!empty($data->name->meterRanking)) {
            $this->data['rank']['current_rank'] = $data->name->meterRanking->currentRank ?? null;
            $this->data['rank']['change_direction'] = $data->name->meterRanking->rankChange->changeDirection ?? null;
            $this->data['rank']['difference'] = $data->name->meterRanking->rankChange->difference ?? null;
        }
    }

    /**
     * Get the age of the person
     *
     * @return int|null
     * @throws Exception
     */
    public function age(): ?int
    {
        if (!$this->isFullCalled && empty($this->data['age'])) {
            $query = <<<GRAPHQL
query Age(\$id: ID!) {
  name(id: \$id) {
    age {
      value
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Age", ["id" => $this->imdb_id]);
            $this->ageParse($data);
        }

        return $this->data['age'];
    }

    /**
     * Parse age
     *
     * @param $data
     * @return void
     */
    private function ageParse($data): void
    {
        if (!empty($data->name->age->value)) {
            $this->data['age'] = $data->name->age->value;
        }
    }

    /**
     * Get birth information
     *
     * @return array|null
     * @throws Exception
     */
    public function birth(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['birth'])) {
            $query = <<<GRAPHQL
query BirthDate(\$id: ID!) {
  name(id: \$id) {
    birthDate {
      dateComponents {
        day
        month
        year
      }
    }
    birthLocation {
      text
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "BirthDate", ["id" => $this->imdb_id]);
            $this->birthParse($data);
        }

        return $this->data['birth'];
    }

    /**
     * Parse birth
     *
     * @param $data
     * @return void
     */
    private function birthParse($data): void
    {
        $birthDate = $data->name->birthDate->dateComponents ?? null;
        if ($birthDate) {
            $day = $birthDate->day ?? null;
            $month = $birthDate->month ?? null;
            $year = $birthDate->year ?? null;

            $this->data['birth'] = [
                "day" => $day,
                "month" => $month,
                "year" => $year,
                "date" => $this->buildDate($day, $month, $year),
                "place" => $data->name->birthLocation->text ?? null
            ];
        }
    }

    /**
     * Get death information
     *
     * @return array|null
     * @throws Exception
     */
    public function death(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['death'])) {
            $query = <<<GRAPHQL
query DeathDate(\$id: ID!) {
  name(id: \$id) {
    deathDate {
      dateComponents {
        day
        month
        year
      }
    }
    deathLocation {
      text
    }
    deathCause {
      text
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "DeathDate", ["id" => $this->imdb_id]);
            $this->deathParse($data);
        }

        return $this->data['death'];
    }

    /**
     * Parse death
     *
     * @param $data
     * @return void
     */
    private function deathParse($data): void
    {
        $deathDate = $data->name->deathDate->dateComponents ?? null;
        if ($deathDate) {
            $day = $deathDate->day ?? null;
            $month = $deathDate->month ?? null;
            $year = $deathDate->year ?? null;

            $this->data['death'] = [
                "day" => $day,
                "month" => $month,
                "year" => $year,
                "date" => $this->buildDate($day, $month, $year),
                "place" => $data->name->deathLocation->text ?? null,
                "cause" => $data->name->deathCause->text ?? null,
            ];
        }
    }

    /**
     * Get the birth name
     *
     * @return string|null
     * @throws Exception
     */
    public function birthName(): ?string
    {
        if (!$this->isFullCalled && empty($this->data['birth_name'])) {
            $query = <<<GRAPHQL
query BirthName(\$id: ID!) {
  name(id: \$id) {
    birthName {
      text
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "BirthName", ["id" => $this->imdb_id]);
            $this->birthNameParse($data);
        }

        return $this->data['birth_name'];
    }

    /**
     * Parse birth name
     *
     * @param $data
     * @return void
     */
    private function birthNameParse($data): void
    {
        if (!empty($data->name->birthName->text)) {
            $this->data['birth_name'] = $data->name->birthName->text;
        }
    }

    /**
     * Get the nicknames
     *
     * @return array|null
     * @throws Exception
     */
    public function nickNames(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['nick_names'])) {
            $query = <<<GRAPHQL
query NickName(\$id: ID!) {
  name(id: \$id) {
    nickNames {
      text
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "NickName", ["id" => $this->imdb_id]);
            $this->nickNamesParse($data);
        }

        return $this->data['nick_names'];
    }

    /**
     * Parse nicknames
     *
     * @param $data
     * @return void
     */
    private function nickNamesParse($data): void
    {
        if ($this->hasArrayItems($data->name->nickNames)) {
            foreach ($data->name->nickNames as $nickName) {
                $this->data['nick_names'][] = $nickName->text;
            }
        }
    }

    /**
     * Get AKA names for a person
     *
     * @return array|null
     * @throws Exception
     */
    public function akaNames(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['aka_names'])) {
            $query = <<<GRAPHQL
query AkaName(\$id: ID!) {
  name(id: \$id) {
    akas(first: 9999) {
      edges {
        node {
          text
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "AkaName", ["id" => $this->imdb_id]);
            $this->akaNamesParse($data);
        }

        return $this->data['aka_names'];
    }

    /**
     * Parse aka names
     *
     * @param $data
     * @return void
     */
    private function akaNamesParse($data): void
    {
        if ($this->hasArrayItems($data->name->akas->edges)) {
            foreach ($data->name->akas->edges as $edge) {
                $this->data['aka_names'][] = $edge->node->text;
            }
        }
    }

    /**
     * Get the body height
     *
     * @return array|null
     * @throws Exception
     */
    public function bodyHeight(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['body_height'])) {
            $query = <<<GRAPHQL
query BodyHeight(\$id: ID!) {
  name(id: \$id) {
    height {
      displayableProperty {
        value {
          plainText
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "BodyHeight", ["id" => $this->imdb_id]);
            $this->bodyHeightParse($data);
        }

        return $this->data['body_height'];
    }

    /**
     * Parse body height
     *
     * @param $data
     * @return void
     */
    private function bodyHeightParse($data): void
    {
        if (!empty($data->name->height->displayableProperty->value->plainText)) {
            $heightParts = explode("(", $data->name->height->displayableProperty->value->plainText);
            $this->data['body_height']["imperial"] = trim($heightParts[0]);
            if (!empty($heightParts[1])) {
                $this->data['body_height']["metric"] = trim($heightParts[1], ")");

                // change to centimeter
                $height = $this->data['body_height']["metric"];
                $height = str_replace(["m", ".", " "], "", $height);
                if (strlen($height) == 2) {
                    $height = $height . '0';
                }
                $this->data['body_height']["metric_cm"] = (int)$height;
            } else {
                $this->data['body_height']["metric"] = null;
                $this->data['body_height']["metric_cm"] = null;
            }
        }
    }

    /**
     * Get the person's mini bio
     *
     * @return array
     * @throws Exception
     */
    public function bio(): array
    {
        if (!$this->isFullCalled && empty($this->data['bio'])) {
            $query = <<<GRAPHQL
query MiniBio(\$id: ID!) {
  name(id: \$id) {
    bios(first: 9999) {
      edges {
        node {
          text {
            plainText
          }
          author {
            plainText
          }
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "MiniBio", ["id" => $this->imdb_id]);
            $this->bioParse($data);
        }

        return $this->data['bio'];
    }

    /**
     * Parse bio
     *
     * @param $data
     * @return void
     */
    private function bioParse($data): void
    {
        if ($this->hasArrayItems($data->name->bios->edges)) {
            foreach ($data->name->bios->edges as $edge) {
                if (empty($edge->node->text->plainText)) {
                    continue;
                }

                $this->data['bio'][] = [
                    'text' => $edge->node->text->plainText,
                    'author' => $edge->node->author->plainText ?? null,
                ];
            }
        }
    }

    /**
     * Get primary professions of this person
     *
     * @return array|null
     * @throws Exception
     */
    public function professions(): ?array
    {
        if (!$this->isFullCalled && empty($this->data['professions'])) {
            $query = <<<GRAPHQL
query Professions(\$id: ID!) {
  name(id: \$id) {
    primaryProfessions {
      category {
        text
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Professions", ["id" => $this->imdb_id]);
            $this->professionsParse($data);
        }

        return $this->data['professions'];
    }

    /**
     * Parse professions
     *
     * @param $data
     * @return void
     */
    private function professionsParse($data): void
    {
        if ($this->hasArrayItems($data->name->primaryProfessions)) {
            foreach ($data->name->primaryProfessions as $primaryProfession) {
                $this->data['professions'][] = $primaryProfession->category->text;
            }
        }
    }

    /**
     * Get spouse(s)
     *
     * @return array|null
     * @throws Exception
     */
    public function spouses(): ?array
    {
        if (empty($this->data['spouses'])) {
            $query = <<<GRAPHQL
query Spouses(\$id: ID!) {
  name(id: \$id) {
    spouses {
      spouse {
        name {
          id
        }
        asMarkdown {
          plainText
        }
      }
      timeRange {
        fromDate {
          dateComponents {
            day
            month
            year
          }
        }
        toDate {
          dateComponents {
            day
            month
            year
          }
        }
        displayableProperty {
          value {
            plainText
          }
        }
      }
      attributes {
        text
      }
      current
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "Spouses", ["id" => $this->imdb_id]);
            $this->spousesParse($data);
        }

        return $this->data['spouses'];
    }

    /**
     * Parse spouses
     *
     * @param $data
     * @return void
     */
    private function spousesParse($data): void
    {
        if ($this->hasArrayItems($data->name->spouses)) {
            foreach ($data->name->spouses as $spouse) {
                if (empty($spouse->spouse->name->id)) {
                    continue;
                }

                // From date
                $fromDate = [
                    "day" => $spouse->timeRange->fromDate->dateComponents->day ?? null,
                    "month" => $spouse->timeRange->fromDate->dateComponents->month ?? null,
                    "year" => $spouse->timeRange->fromDate->dateComponents->year ?? null
                ];

                // To date
                $toDate = [
                    "day" => $spouse->timeRange->toDate->dateComponents->day ?? null,
                    "month" => $spouse->timeRange->toDate->dateComponents->month ?? null,
                    "year" => $spouse->timeRange->toDate->dateComponents->year ?? null
                ];

                // Comments and children
                $comment = [];
                $children = 0;
                if ($this->hasArrayItems($spouse->attributes)) {
                    foreach ($spouse->attributes as $attribute) {
                        if (!empty($attribute->text)) {
                            if (stripos($attribute->text, "child") !== false) {
                                $children = (int)preg_replace('/[^0-9]/', '', $attribute->text);
                            } else {
                                $comment[] = $attribute->text;
                            }
                        }
                    }
                }

                $this->data['spouses'][] = [
                    'id' => $spouse->spouse->name->id,
                    'name' => $spouse->spouse->asMarkdown->plainText ?? null,
                    'from' => $fromDate,
                    'to' => $toDate,
                    'date' => $spouse->timeRange->displayableProperty->value->plainText ?? null,
                    'comment' => $comment,
                    'children' => $children,
                    'current' => $spouse->current
                ];
            }
        }
    }

    /**
     * Get the Children
     *
     * @return array|null
     * @throws Exception
     */
    public function children(): ?array
    {
        if (empty($this->data['children'])) {
            $this->nameDetailsParse("CHILDREN", 'children');
        }

        return $this->data['children'];
    }

    /**
     * Get the Parents
     *
     * @return array|null
     * @throws Exception
     */
    public function parents(): ?array
    {
        if (empty($this->data['parents'])) {
            $this->nameDetailsParse("PARENTS", 'parents');
        }

        return $this->data['parents'];
    }

    /**
     * Get the relatives
     *
     * @return array|null
     * @throws Exception
     */
    public function relatives(): ?array
    {
        if (empty($this->data['relatives'])) {
            $this->nameDetailsParse("OTHERS", 'relatives');
        }

        return $this->data['relatives'];
    }

    /**
     * Get the Trivia
     *
     * @return array|null
     * @throws Exception
     */
    public function trivia(): ?array
    {
        if (empty($this->data['trivia'])) {
            $this->dataParse("trivia");
        }

        return $this->data['trivia'];
    }

    /**
     * Get the Personal Quotes
     *
     * @return array|null
     * @throws Exception
     */
    public function quotes(): ?array
    {
        if (empty($this->data['quotes'])) {
            $this->dataParse("quotes");
        }

        return $this->data['quotes'];
    }

    /**
     * Get the "trademarks" of the person
     *
     * @return array|null
     * @throws Exception
     */
    public function trademarks(): ?array
    {
        if (empty($this->data['trademarks'])) {
            $this->dataParse("trademarks");
        }

        return $this->data['trademarks'];
    }

    /**
     * Get the salary list
     *
     * @return array|null
     * @throws Exception
     */
    public function salaries(): ?array
    {
        if (empty($this->data['salaries'])) {
            $query = <<<GRAPHQL
title {
  id
  titleText {
    text
  }
  releaseYear {
    year
  }
}
amount {
  amount
  currency
}
attributes {
  text
}
GRAPHQL;
            $data = $this->getAllData("Salaries", "titleSalaries", $query);
            $this->salariesParse($data);
        }

        return $this->data['salaries'];
    }

    /**
     * Parse salaries
     *
     * @param $data
     * @return void
     */
    private function salariesParse($data): void
    {
        if (count($data) > 0) {
            foreach ($data as $edge) {
                if (empty($edge->node->title->id)) {
                    continue;
                }

                $comments = null;
                if (!empty($edge->node->attributes)) {
                    foreach ($edge->node->attributes as $attribute) {
                        if (!empty($attribute->text)) {
                            $comments[] = $attribute->text;
                        }
                    }
                }

                $this->data['salaries'][] = [
                    'id' => $edge->node->title->id,
                    'title' => $edge->node->title->titleText->text ?? null,
                    'year' => $edge->node->title->releaseYear->year ?? null,
                    'amount' => $edge->node->amount->amount ?? null,
                    'currency' => $edge->node->amount->currency ?? null,
                    'comment' => $comments
                ];
            }
        }
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
  name(id: \$id) {
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
        if ($this->hasArrayItems($data->name->images->edges)) {
            $images = [];
            foreach ($data->name->images->edges as $edge) {
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
     * @return array|null
     * @throws Exception
     */
    public function videos(int $limit = 9999): ?array
    {
        if (empty($this->data['videos'])) {
            $query = <<<GRAPHQL
query Video(\$id: ID!) {
  name(id: \$id) {
    primaryVideos(first: $limit) {
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
          createdDate
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

            if ($this->hasArrayItems($data->name->primaryVideos->edges)) {
                $videoClass = new Video();
                $this->data['videos'] = $videoClass->parseVideoResults($data->name->primaryVideos->edges);
            }
        }

        return $this->data['videos'];
    }

    /**
     * Get news items about this name, max 100 items!
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
  name(id: \$id) {
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
            if ($this->hasArrayItems($data->name->news->edges)) {
                $newsClass = new News();
                $this->data['news'] = $newsClass->parseNewsResults($data->name->news->edges);
            }
        }

        return $this->data['news'];
    }

    /**
     * All prestigious title credits for this person
     *
     * @param int $limit
     * @return array|null
     * @throws Exception
     */
    public function creditKnownFor(int $limit = 4): ?array
    {
        if (empty($this->data['credit_known_for'])) {
            $query = <<<GRAPHQL
query KnownFor(\$id: ID!) {
  name(id: \$id) {
    knownFor(first: $limit) {
      edges {
        node{
          credit {
            title {
              id
              titleText {
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
            }
            ... on Cast {
              characters {
                name
              }
            }
          }
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->graphql->query($query, "KnownFor", ["id" => $this->imdb_id]);
            $this->creditKnownForParse($data);
        }

        return $this->data['credit_known_for'];
    }

    /**
     * Parse Credit known
     *
     * @param $data
     * @return void
     */
    private function creditKnownForParse($data): void
    {
        if ($this->hasArrayItems($data->name->knownFor->edges)) {
            $items = [];
            foreach ($data->name->knownFor->edges as $edge) {
                if (empty($edge->node->credit->title->id) || empty($edge->node->credit->title->titleText->text)) {
                    continue;
                }

                $characters = [];
                if ($this->hasArrayItems($edge->node->credit->characters)) {
                    foreach ($edge->node->credit->characters as $character) {
                        if (!empty($character->name)) {
                            $characters[] = $character->name;
                        }
                    }
                }

                $items[] = [
                    'id' => $edge->node->credit->title->id,
                    'title' => $edge->node->credit->title->titleText->text,
                    'year' => $edge->node->credit->title->releaseYear->year ?? null,
                    'end_year' => $edge->node->credit->title->releaseYear->endYear ?? null,
                    'characters' => $characters,
                    'image' => $this->parseImage($edge->node->credit->title->primaryImage)
                ];
            }

            $this->data['credit_known_for'] = $items;
        }
    }

    public array $creditCategoryIds = [
        'director' => 'director',
        'writer' => 'writer',
        'actress' => 'actress',
        'actor' => 'actor',
        'producer' => 'producer',
        'composer' => 'composer',
        'cinematographer' => 'cinematographer',
        'editor' => 'editor',
        'casting_director' => 'castingDirector',
        'production_designer' => 'productionDesigner',
        'art_director' => 'artDirector',
        'set_decorator' => 'setDecorator',
        'costume_designer' => 'costumeDesigner',
        'make_up_department' => 'makeUpDepartment',
        'production_manager' => 'productionManager',
        'assistant_director' => 'assistantDirector',
        'art_department' => 'artDepartment',
        'sound_department' => 'soundDepartment',
        'special_effects' => 'specialEffects',
        'visual_effects' => 'visualEffects',
        'stunts' => 'stunts',
        'choreographer' => 'choreographer',
        'camera_department' => 'cameraDepartment',
        'animation_department' => 'animationDepartment',
        'casting_department' => 'castingDepartment',
        'costume_department' => 'costumeDepartment',
        'editorial_department' => 'editorialDepartment',
        'electrical_department' => 'electricalDepartment',
        'location_management' => 'locationManagement',
        'music_department' => 'musicDepartment',
        'production_department' => 'productionDepartment',
        'script_department' => 'scriptDepartment',
        'transportation_department' => 'transportationDepartment',
        'miscellaneous' => 'miscellaneous',
        'thanks' => 'thanks',
        'executive' => 'executive',
        'legal' => 'legal',
        'soundtrack' => 'soundtrack',
        'manager' => 'manager',
        'assistant' => 'assistant',
        'talent_agent' => 'talentAgent',
        'self' => 'self',
        'publicist' => 'publicist',
        'music_artist' => 'musicArtist',
        'podcaster' => 'podcaster',
        'archive_footage' => 'archiveFootage',
        'archive_sound' => 'archiveSound',
        'costume_supervisor' => 'costumeSupervisor',
        'hair_stylist' => 'hairStylist',
        'intimacy_coordinator' => 'intimacyCoordinator',
        'make_up_artist' => 'makeUpArtist',
        'music_supervisor' => 'musicSupervisor',
        'property_master' => 'propertyMaster',
        'script_supervisor' => 'scriptSupervisor',
        'showrunner' => 'showrunner',
        'stunt_coordinator' => 'stuntCoordinator',
        'accountant' => 'accountant'
    ];

    /**
     * Get all credits for a person
     *
     * @return array|null
     * @throws Exception
     */
    public function credits(): ?array
    {
        if (empty($this->data['credits'])) {
            foreach ($this->creditCategoryIds as $categoryId) {
                $this->data['credits'][$categoryId] = [];
            }

            $query = <<<GRAPHQL
category {
  id
}
title {
  id
  titleText {
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
}
... on Cast {
  characters {
    name
  }
}
... on Crew {
  jobs {
    text
  }
}
GRAPHQL;
            $data = $this->getAllData("Credits", "credits", $query);
            $this->creditsParse($data);
        }

        return $this->data['credits'];
    }

    /**
     * Parse Credits
     *
     * @param $data
     * @return void
     */
    private function creditsParse($data): void
    {
        if ($this->hasArrayItems($data)) {
            foreach ($data as $edge) {
                if (empty($edge->node->title->id)) {
                    continue;
                }

                $characters = [];
                if (isset($edge->node->characters) && $this->hasArrayItems($edge->node->characters)) {
                    foreach ($edge->node->characters as $character) {
                        if (!empty($character->name)) {
                            $characters[] = $character->name;
                        }
                    }
                }

                $jobs = [];
                if (isset($edge->node->jobs) && $this->hasArrayItems($edge->node->jobs)) {
                    foreach ($edge->node->jobs as $job) {
                        if (!empty($job->text)) {
                            $jobs[] = $job->text;
                        }
                    }
                }

                $this->data['credits'][$this->creditCategoryIds[$edge->node->category->id]][] = [
                    'id' => $edge->node->title->id,
                    'title' => $edge->node->title->titleText->text ?? null,
                    'type' => $edge->node->title->titleType->text ?? null,
                    'year' => $edge->node->title->releaseYear->year ?? null,
                    'end_year' => $edge->node->title->releaseYear->endYear ?? null,
                    'characters' => $characters,
                    'jobs' => $jobs,
                    'image' => $this->parseImage($edge->node->title->primaryImage)
                ];
            }
        }
    }

    /**
     * Get all awards for a name
     *
     * @param bool $winsOnly
     * @param string $event
     * @return array|null
     * @throws Exception
     */
    public function awards(bool $winsOnly = false, string $event = ""): ?array
    {
        if (empty($this->data['awards'])) {
            $filter = $this->awardFilter($winsOnly, $event);
            $query = <<<GRAPHQL
award {
  id
  event {
    id
    text
  }
  text
  category {
    text
  }
  eventEdition {
    year
  }
  notes {
    plainText
  }
}
isWinner
awardedEntities {
  ... on AwardedNames {
    secondaryAwardTitles {
      title {
        id
        titleText {
          text
        }
        primaryImage {
          url
          width
          height
        }
      }
      note {
        plainText
      }
    }
  }
}
GRAPHQL;
            $data = $this->getAllData("Award", "awardNominations", $query, $filter);

            $winnerCount = 0;
            $nomineeCount = 0;
            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $eventName = $edge->node->award->event->text ?? null;
                    $isWinner = $edge->node->isWinner;
                    $conclusion = $isWinner === true ? "Winner" : "Nominee";
                    $isWinner === true ? $winnerCount++ : $nomineeCount++;

                    // credited titles
                    $titles = [];
                    if (isset($edge->node->awardedEntities->secondaryAwardTitles) && $this->hasArrayItems($edge->node->awardedEntities->secondaryAwardTitles)) {
                        foreach ($edge->node->awardedEntities->secondaryAwardTitles as $title) {
                            $titles[] = [
                                'id' => $title->title->id,
                                'title' => $title->title->titleText->text ?? null,
                                'note' => isset($title->note->plainText) ? trim($title->note->plainText, " ()") : null,
                                'image' => $this->parseImage($title->title->primaryImage)
                            ];
                        }
                    }

                    $eventId = $edge->node->award->event->id;
                    $this->data['awards']['events'][$eventId][] = [
                        'id' => $edge->node->award->event->id ?? null,
                        'name' => $edge->node->award->text ?? null,
                        'event_name' => $eventName,
                        'year' => $edge->node->award->eventEdition->year ?? null,
                        'category' => $edge->node->award->category->text ?? null,
                        'notes' => $edge->node->award->notes->plainText ?? null,
                        'titles' => $titles,
                        'is_winner' => $isWinner,
                        'conclusion' => $conclusion
                    ];
                }
            }

            $this->data['awards']['stats'] = [
                'win' => $winnerCount,
                'nom' => $nomineeCount
            ];
        }

        return $this->data['awards'];
    }

    /**
     * Build award filter string
     *
     * @param $winsOnly boolean
     * @param $event string eventId
     * @return string $filter
     */
    public function awardFilter(bool $winsOnly, string $event): string
    {
        $filter = ', sort: {by: PRESTIGIOUS, order: DESC}';
        if (!empty($event) || $winsOnly === true) {
            $filter .= ', filter:{';
            if ($winsOnly === true) {
                $filter .= 'wins:WINS_ONLY';
                if (empty($event)) {
                    $filter .= '}';
                } else {
                    $filter .= ', events:"' . trim($event) . '"}';
                }
            } else {
                $filter .= 'events:"' . trim($event) . '"}';
            }
        }

        return $filter;
    }

    /**
     * Print media about this person
     *
     * @return array|null
     * @throws Exception
     */
    public function pubPrints(): ?array
    {
        if (empty($this->data['pub_prints'])) {
            $filter = ', filter: {categories: ["namePrintBiography"]}';
            $query = <<<GRAPHQL
... on NamePrintBiography {
  title {
    text
  }
  authors {
    plainText
  }
  isbn
  publisher
}
GRAPHQL;
            $data = $this->getAllData("PubPrint", "publicityListings", $query, $filter);

            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $authors = [];
                    if (!empty($edge->node->authors)) {
                        foreach ($edge->node->authors as $author) {
                            if (!empty($author->plainText)) {
                                $authors[] = $author->plainText;
                            }
                        }
                    }

                    $this->data['pub_prints'][] = [
                        "title" => $edge->node->title->text ?? null,
                        "author" => $authors,
                        "publisher" => $edge->node->publisher ?? null,
                        "isbn" => $edge->node->isbn ?? null
                    ];
                }
            }
        }

        return $this->data['pub_prints'];
    }

    /**
     * Biographical Movies
     *
     * @return array|null
     * @throws Exception
     */
    public function pubMovies(): ?array
    {
        if (empty($this->data['pub_movies'])) {
            $filter = ', filter: {categories: ["nameFilmBiography"]}';
            $query = <<<GRAPHQL
... on NameFilmBiography {
  title {
    titleText {
      text
    }
    id
    releaseYear {
      year
    }
    series {
      displayableEpisodeNumber {
        displayableSeason {
          text
        }
        episodeNumber {
          text
        }
      }
      series {
        titleText {
          text
        }
      }
    }
  }
}
GRAPHQL;
            $data = $this->getAllData("PubFilm", "publicityListings", $query, $filter);
            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $this->data['pub_movies'][] = [
                        "id" => $edge->node->title->id ?? null,
                        "title" => $edge->node->title->titleText->text ?? null,
                        "year" => $edge->node->title->releaseYear->year ?? null,
                        "series_title" => $edge->node->title->series->series->titleText->text ?? null,
                        "series_season" => $edge->node->title->series->displayableEpisodeNumber->displayableSeason->text ?? null,
                        "series_episode" => $edge->node->title->series->displayableEpisodeNumber->episodeNumber->text ?? null,
                    ];
                }
            }
        }

        return $this->data['pub_movies'];
    }

    /**
     * Portrayal listings about this person
     *
     * @return array|null
     * @throws Exception
     */
    public function pubPortrayal(): ?array
    {
        if (empty($this->data['pub_portrayal'])) {
            $filter = ', filter: {categories: ["namePortrayal"]}';
            $query = <<<GRAPHQL
... on NamePortrayal {
  title {
    titleText {
      text
    }
    id
    releaseYear {
      year
    }
  }
}
GRAPHQL;
            $data = $this->getAllData("PubPortrayal", "publicityListings", $query, $filter);

            if (count($data) > 0) {
                foreach ($data as $edge) {
                    $this->data['pub_portrayal'][] = [
                        'id' => $edge->node->title->id ?? null,
                        'title' => $edge->node->title->titleText->text ?? null,
                        'year' => $edge->node->title->releaseYear->year ?? null
                    ];
                }
            }
        }

        return $this->data['pub_portrayal'];
    }

    /**
     * Get the Publicity Articles of this name
     *
     * @return array|null
     * @throws Exception
     */
    public function pubArticle(): ?array
    {
        if (empty($this->data['pub_article'])) {
            $this->data['pub_article'] = $this->pubOtherListing("PublicityArticle");
        }

        return $this->data['pub_article'];
    }

    /**
     * Get the Publicity Interviews of this name
     *
     * @return array|null
     * @throws Exception
     */
    public function pubInterview(): ?array
    {
        if (empty($this->data['pub_interview'])) {
            $this->data['pub_interview'] = $this->pubOtherListing("PublicityInterview");
        }

        return $this->data['pub_interview'];
    }

    /**
     * Get the Publicity Magazines of this name
     *
     * @return array|null
     * @throws Exception
     */
    public function pubMagazine(): ?array
    {
        if (empty($this->data['pub_magazine'])) {
            $this->data['pub_magazine'] = $this->pubOtherListing("PublicityMagazineCover");
        }

        return $this->data['pub_magazine'];
    }

    /**
     * Get the Publicity Pictorials of this name
     *
     * @return array|null
     * @throws Exception
     */
    public function pubPictorial(): ?array
    {
        if (empty($this->data['pub_pictorial'])) {
            $this->data['pub_pictorial'] = $this->pubOtherListing("PublicityPictorial");
        }
        return $this->data['pub_pictorial'];
    }

    /**
     * Other works of this person
     *
     * @return array|null
     * @throws Exception
     */
    public function otherWorks(): ?array
    {
        if (empty($this->data['other_works'])) {
            $query = <<<GRAPHQL
category {
  text
}
fromDate
toDate
text {
  plainText
}
GRAPHQL;
            $data = $this->getAllData("OtherWorks", "otherWorks", $query);
            if (count($data) > 0) {
                foreach ($data as $edge) {
                    // From date
                    $fromDate = [
                        "day" => $edge->node->fromDate->day ?? null,
                        "month" => $edge->node->fromDate->month ?? null,
                        "year" => $edge->node->fromDate->year ?? null
                    ];
                    // To date
                    $toDate = [
                        "day" => $edge->node->toDate->day ?? null,
                        "month" => $edge->node->toDate->month ?? null,
                        "year" => $edge->node->toDate->year ?? null
                    ];
                    $this->data['other_works'][] = [
                        "category" => $edge->node->category->text ?? null,
                        "from" => $fromDate,
                        "to" => $toDate,
                        "text" => $edge->node->text->plainText ?? null
                    ];
                }
            }
        }

        return $this->data['other_works'];
    }

    /**
     * external websites with info of this name, excluding external reviews.
     *
     * @return array|null
     * @throws Exception
     */
    public function externalSites(): ?array
    {
        $categoryIds = [
            'official' => 'official',
            'video' => 'video',
            'photo' => 'photo',
            'sound' => 'sound',
            'misc' => 'misc'
        ];

        if (empty($this->data['external_sites'])) {
            foreach ($categoryIds as $categoryId) {
                $this->data['external_sites'][$categoryId] = [];
            }
            $query = <<<GRAPHQL
label
url
externalLinkCategory {
  id
}
externalLinkLanguages {
  text
}
GRAPHQL;
            $filter = ' filter: {excludeCategories: "review"}';
            $edges = $this->getAllData("ExternalSites", "externalLinks", $query, $filter);
            if (count($edges) > 0) {
                foreach ($edges as $edge) {
                    $language = [];
                    if (!empty($edge->node->externalLinkLanguages)) {
                        foreach ($edge->node->externalLinkLanguages as $lang) {
                            if (!empty($lang->text)) {
                                $language[] = $lang->text;
                            }
                        }
                    }

                    $this->data['external_sites'][$categoryIds[$edge->node->externalLinkCategory->id]][] = [
                        'label' => $edge->node->label ?? null,
                        'url' => $edge->node->url ?? null,
                        'language' => $language
                    ];
                }
            }
        }

        return $this->data['external_sites'];
    }


    /***************************************[ Helper Methods ]***************************************/
    /**
     * Get all edges of a field in the name type
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
        $query = <<<GRAPHQL
query $queryName(\$id: ID!, \$after: ID) {
  name(id: \$id) {
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
GRAPHQL;
        $fullQuery = implode("\n", array_map('trim', explode("\n", $query)));

        // Results are paginated, so loop until we've got all the data
        $endCursor = null;
        $hasNextPage = true;
        $edges = [];
        while ($hasNextPage) {
            $data = $this->graphql->query($fullQuery, $queryName, ["id" => $this->imdb_id, "after" => $endCursor]);
            if (isset($data->name->{$fieldName})) {
                $edges = array_merge($edges, $data->name->{$fieldName}->edges);
                $hasNextPage = $data->name->{$fieldName}->pageInfo->hasNextPage;
                $endCursor = $data->name->{$fieldName}->pageInfo->endCursor;
            }
        }

        return $edges;
    }

    /**
     * Parse children, parents, relatives
     *
     * @param string $name
     * @param string $arrayName
     * @return void
     * @throws Exception
     */
    protected function nameDetailsParse(string $name, string $arrayName): void
    {
        $filter = ', filter: {relationshipTypes: ' . $name . '}';
        $query = <<<GRAPHQL
relationName {
  name {
    id
    nameText {
      text
    }
  }
  nameText
}
relationshipType {
  text
}
GRAPHQL;
        $data = $this->getAllData("Data", "relations", $query, $filter);

        if (count($data) > 0) {
            foreach ($data as $edge) {
                if (!empty($edge->node->relationName->name)) {
                    $name = $edge->node->relationName->name->nameText->text ?? null;
                } else {
                    $name = $edge->node->relationName->nameText ?? null;
                }

                if (empty($name)) {
                    continue;
                }

                $this->data[$arrayName][] = [
                    'id' => $edge->node->relationName->name->id ?? null,
                    'name' => $name,
                    'type' => $edge->node->relationshipType->text ?? null
                ];
            }
        }
    }

    /**
     * Parse Trivia, Quotes and Trademarks
     *
     * @param string $name
     * @return void
     * @throws Exception
     */
    protected function dataParse(string $name): void
    {
        $query = <<<GRAPHQL
text {
  plainText
}
GRAPHQL;
        $data = $this->getAllData("Data", $name, $query);
        if (count($data) > 0) {
            foreach ($data as $edge) {
                if (!empty($edge->node->text->plainText)) {
                    $this->data[$name][] = $edge->node->text->plainText;
                }
            }
        }
    }

    /**
     * helper for Article, Interview, Magazine and Pictorial publicity listings about this person
     *
     * @param string $listingType
     * @return array
     * @throws Exception
     */
    protected function pubOtherListing(string $listingType): array
    {
        $results = array();
        $filter = ', filter: {categories: ["' . lcfirst($listingType) . '"]}';
        $query = <<<GRAPHQL
... on $listingType {
  authors {
    plainText
  }
  publication
  reference
  date
  region {
    id
  }
  title {
    text
  }
}
GRAPHQL;
        $data = $this->getAllData($listingType, "publicityListings", $query, $filter);
        if (count($data) > 0) {
            foreach ($data as $edge) {
                $date = [
                    'day' => $edge->node->date->day ?? null,
                    'month' => $edge->node->date->month ?? null,
                    'year' => $edge->node->date->year ?? null
                ];

                $authors = [];
                if (isset($edge->node->authors) && $this->hasArrayItems($edge->node->authors)) {
                    foreach ($edge->node->authors as $author) {
                        if (!empty($author->plainText)) {
                            $authors[] = $author->plainText;
                        }
                    }
                }

                $results[] = [
                    'publication' => $edge->node->publication ?? null,
                    'region_id' => $edge->node->region->id ?? null,
                    'title' => $edge->node->title->text ?? null,
                    'date' => $date,
                    'reference' => $edge->node->reference ?? null,
                    'authors' => $authors
                ];
            }
        }

        return $results;
    }

}

