<?php

namespace Hooshid\ImdbScraper;

use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Name extends Base
{
    private ?string $imdb_id;

    protected array $data = [
        'canonical_id' => null,
        'full_name' => null,
        'photo' => [],
        'rank' => null,
        'birth' => [],
        'death' => [],
        'birth_name' => null,
        'nick_names' => [],
        'aka_names' => [],
        'body_height' => [],
        'bio' => [],
        'professions' => [],
    ];

    /**
     * @param string $id IMDB ID to use for data retrieval
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(string $id, Config $config = null)
    {
        parent::__construct($config);
        $this->imdb_id = $id;
    }

    /***************************************[ Main Methods ]***************************************/

    /**
     * Set up the URL to the person page
     *
     * @return string
     */
    public function mainUrl(): string
    {
        return "https://" . $this->imdbSiteUrl . "/name/" . $this->imdb_id . "/";
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
     * this function return full extracted data in single json
     *
     * @return array
     */
    public function full(): array
    {
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
    }
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
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
        $this->photoParse($data);
        $this->rankParse($data);
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
     * Check redirect to another id or not.
     *
     * @return string|null
     */
    public function canonicalId(): ?string
    {
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
            $nameImdbId = $data->name->meta->canonicalId;
            if ($nameImdbId != $this->imdb_id) {
                $this->data['canonical_id'] = $nameImdbId;
            }
        }
    }

    /**
     * Get the name of the person
     *
     * @return string|null
     */
    public function fullName(): ?string
    {
        if (empty($this->data['full_name'])) {
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
        $this->data['full_name'] = $this->cleanString($data->name->nameText->text ?? '');
    }

    /**
     * Get photo
     *
     * @return array
     */
    public function photo(): array
    {
        if (empty($this->data['photo'])) {
            $query = <<<EOF
query PrimaryImage(\$id: ID!) {
  name(id: \$id) {
    primaryImage {
      url
    }
  }
}
EOF;

            $data = $this->graphql->query($query, "PrimaryImage", ["id" => $this->imdb_id]);
            $this->photoParse($data);
        }

        return $this->data['photo'];
    }

    /**
     * Parse photo and image url
     *
     * @param $data
     * @return void
     */
    private function photoParse($data): void
    {
        if (!empty($data->name->primaryImage->url)) {
            $this->data['photo'] = $this->imageUrl($data->name->primaryImage->url);
        }
    }

    /**
     * Get current popularity rank of a person
     *
     * @return array
     */
    public function rank(): array
    {
        if (empty($this->data['rank'])) {
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
     * Get birth information
     *
     * @return array
     */
    public function birth(): array
    {
        if (empty($this->data['birth'])) {
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
     * @param $data
     * @return void
     */
    private function birthParse($data): void
    {
        $birthDate = $data->name->birthDate->dateComponents ?? null;
        if ($birthDate) {
            $day = $birthDate->day ?? null;
            $monthInt = $birthDate->month ?? null;
            $year = $birthDate->year ?? null;
            $monthName = $monthInt ? date("F", mktime(0, 0, 0, $monthInt, 10)) : null;
            $full_date = (!empty($day) && !empty($monthInt) && !empty($year)) ? date("Y-m-d", mktime(0, 0, 0, $monthInt, $day, $year)) : null;
            $place = $this->cleanString($data->name->birthLocation->text ?? '');

            $this->data['birth'] = [
                "day" => $day,
                "month" => $monthName,
                "mon" => $monthInt,
                "year" => $year,
                "date" => $full_date,
                "place" => $place
            ];
        }
    }

    /**
     * Get death information
     *
     * @return array
     */
    public function death(): array
    {
        if (empty($this->data['death'])) {
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
            $monthInt = $deathDate->month ?? null;
            $year = $deathDate->year ?? null;
            $monthName = $monthInt ? date("F", mktime(0, 0, 0, $monthInt, 10)) : null;
            $full_date = (!empty($day) && !empty($monthInt) && !empty($year)) ? date("Y-m-d", mktime(0, 0, 0, $monthInt, $day, $year)) : null;
            $place = $this->cleanString($data->name->deathLocation->text ?? '');
            $cause = $this->cleanString($data->name->deathCause->text ?? '');

            $this->data['death'] = [
                "day" => $day,
                "month" => $monthName,
                "mon" => $monthInt,
                "year" => $year,
                "date" => $full_date,
                "place" => $place,
                "cause" => $cause,
            ];
        }
    }

    /**
     * Get the birth name
     *
     * @return string|null
     */
    public function birthName(): ?string
    {
        if (empty($this->data['birth_name'])) {
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
        $this->data['birth_name'] = $data->name->birthName->text ?? null;
    }

    /**
     * Get the nicknames
     *
     * @return array
     */
    public function nickNames(): array
    {
        if (empty($this->data['nick_names'])) {
            if (empty($this->nickName)) {
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
                if (!empty($nickName->text)) {
                    $this->data['nick_names'][] = $nickName->text;
                }
            }
        }
    }

    /**
     * Get alternative names for a person
     *
     * @return array
     */
    public function akaNames(): array
    {
        if (empty($this->data['aka_names'])) {
            if (empty($this->professions)) {
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
                if (isset($edge->node->text)) {
                    $this->data['aka_names'][] = $edge->node->text;
                }
            }
        }
    }

    /**
     * Get the body height
     *
     * @return array
     */
    public function bodyHeight(): array
    {
        if (empty($this->data['body_height'])) {
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
            $imperial = str_replace("″", '"', $heightParts[0]);
            $imperial = str_replace("′", "'", $imperial);
            $this->data['body_height']["imperial"] = trim($imperial);
            if (!empty($heightParts[1])) {
                $this->data['body_height']["metric"] = trim($heightParts[1], ")");
            } else {
                $this->data['body_height']["metric"] = null;
            }

            // change to centimeter
            $height = $this->data['body_height']["metric"];
            $height = str_replace(["m", ".", " "], "", $height);
            if (strlen($height) == '2') {
                $height = $height . '0';
            }
            $this->data['body_height']["metric_cm"] = (int)$height;
        }
    }

    /**
     * Get the person's mini bio
     *
     * @return array
     */
    public function bio(): array
    {
        if (empty($this->data['bio'])) {
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
                $bio["text"] = $edge->node->text->plainText ?? null;
                $bioAuthor = '';
                if (!empty($edge->node->author) and !empty($edge->node->author->plainText)) {
                    $bioAuthor = $edge->node->author->plainText;
                }
                $bio["author"]["url"] = '';
                $bio["author"]["name"] = $bioAuthor;

                $this->data['bio'][] = $bio;
            }
        }
    }

    /**
     * Get primary professions of this person
     *
     * @return array
     */
    public function professions(): array
    {
        if (empty($this->data['professions'])) {
            if (empty($this->professions)) {
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
                if (!empty($primaryProfession->category->text)) {
                    $this->data['professions'][] = $primaryProfession->category->text;
                }
            }
        }
    }


}

