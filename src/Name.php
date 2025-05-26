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
        'images' => null,
        'videos' => null,
        'news' => null,
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

    /***************************************[ Main Methods ]***************************************/

    /**
     * This function returns the full extracted data in a single JSON-compatible array.
     *
     * @return array
     * @throws Exception
     */
    public function full(): array
    {
        if ($this->isFullCalled) {
            return $this->data;
        }
        $this->isFullCalled = true;

        $query = <<<EOF
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
EOF;
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

        return $this->data;
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
     * Check redirect to another id or not.
     *
     * @return string|null
     * @throws Exception
     */
    public function canonicalId(): ?string
    {
        if (!$this->isFullCalled) {
            $query = <<<EOF
query Redirect(\$id: ID!) {
  name(id: \$id) {
    meta {
      canonicalId
    }
  }
}
EOF;
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
            $query = <<<EOF
query Name(\$id: ID!) {
  name(id: \$id) {
    nameText {
      text
    }
  }
}
EOF;
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
            $query = <<<EOF
query Image(\$id: ID!) {
  name(id: \$id) {
    primaryImage {
      url
      width
      height
    }
  }
}
EOF;

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
            $query = <<<EOF
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
EOF;

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
            $query = <<<EOF
query Age(\$id: ID!) {
  name(id: \$id) {
    age {
      value
    }
  }
}
EOF;
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
            $query = <<<EOF
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
EOF;
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
            $query = <<<EOF
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
EOF;
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
            $query = <<<EOF
query BirthName(\$id: ID!) {
  name(id: \$id) {
    birthName {
      text
    }
  }
}
EOF;
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
            $query = <<<EOF
query NickName(\$id: ID!) {
  name(id: \$id) {
    nickNames {
      text
    }
  }
}
EOF;
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
        if (!empty($data->name->nickNames)) {
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
            $query = <<<EOF
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
EOF;
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
        if (!empty($data->name->akas->edges)) {
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
            $query = <<<EOF
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
EOF;
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
            $query = <<<EOF
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
EOF;
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
        if (!empty($data->name->bios->edges)) {
            foreach ($data->name->bios->edges as $edge) {
                $this->data['bio'][] = [
                    'text' => $edge->node->text->plainText ?? null,
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
            $query = <<<EOF
query Professions(\$id: ID!) {
  name(id: \$id) {
    primaryProfessions {
      category {
        text
      }
    }
  }
}
EOF;
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
        if (!empty($data->name->primaryProfessions)) {
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
        if (!$this->isFullCalled && empty($this->data['spouses'])) {
            $query = <<<EOF
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
EOF;
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
        if (!empty($data->name->spouses)) {
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
                if (isset($spouse->attributes) && is_array($spouse->attributes) && count($spouse->attributes) > 0) {
                    foreach ($spouse->attributes as $key => $attribute) {
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
                    'date_text' => $spouse->timeRange->displayableProperty->value->plainText ?? null,
                    'comment' => $comment,
                    'children' => $children,
                    'current' => $spouse->current
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
        if (!$this->isFullCalled && empty($this->data['images'])) {
            $query = <<<EOF
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
EOF;
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
        if (!empty($data->name->images->edges)) {
            $images = [];
            foreach ($data->name->images->edges as $edge) {
                if (empty($edge->node->id) || empty($edge->node->url)) {
                    continue;
                }

                // Titles
                $titles = [];
                if (!empty($edge->node->titles)) {
                    foreach ($edge->node->titles as $title) {
                        $titles[] = [
                            'id' => $title->id ?? null,
                            'title' => $title->titleText->text ?? null
                        ];
                    }
                }

                // Names
                $names = [];
                if (!empty($edge->node->names)) {
                    foreach ($edge->node->names as $name) {
                        $names[] = [
                            'id' => $name->id ?? null,
                            'name' => $name->nameText->text ?? null
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
        if (!$this->isFullCalled && empty($this->data['videos'])) {
            $query = <<<EOF
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
EOF;
            $data = $this->graphql->query($query, "Video", ["id" => $this->imdb_id]);
            $this->videosParse($data);
        }

        return $this->data['videos'];
    }

    /**
     * Parse videos
     *
     * @param $data
     * @return void
     */
    private function videosParse($data): void
    {
        if (!empty($data->name->primaryVideos->edges)) {
            $videos = [];
            foreach ($data->name->primaryVideos->edges as $edge) {
                if (empty($edge->node->id) or empty($edge->node->name->value)) {
                    continue;
                }

                $videos[] = [
                    'id' => $edge->node->id,
                    'playback_url' => $this->makeUrl('video', $edge->node->id),
                    'created_date' => $edge->node->createdDate ?? null,
                    'runtime_formatted' => $this->secondsToTimeFormat($edge->node->runtime->value),
                    'runtime_seconds' => $edge->node->runtime->value ?? null,
                    'title' => $edge->node->name->value,
                    'description' => $edge->node->description->value ?? null,
                    'content_type' => $edge->node->contentType->displayName->value,
                    'thumbnail' => $this->parseImage($edge->node->thumbnail),
                    'primary_title' => [
                        'id' => $edge->node->primaryTitle->id,
                        'title' => $edge->node->primaryTitle->titleText->text ?? null,
                        'year' => $edge->node->primaryTitle->releaseYear->year ?? null,
                        'image' => $this->parseImage($edge->node->primaryTitle->primaryImage)
                    ],
                ];
            }

            $this->data['videos'] = $videos;
        }
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
        if (!$this->isFullCalled && empty($this->data['news'])) {
            $query = <<<EOF
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
EOF;
            $data = $this->graphql->query($query, "News", ["id" => $this->imdb_id]);
            $this->newsParse($data);
        }

        return $this->data['news'];
    }

    /**
     * Parse news
     *
     * @param $data
     * @return void
     */
    private function newsParse($data): void
    {
        if (!empty($data->name->news->edges)) {
            $newsListItems = [];
            foreach ($data->name->news->edges as $edge) {
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

            $this->data['news'] = $newsListItems;
        }
    }
}

