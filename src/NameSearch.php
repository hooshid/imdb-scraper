<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\GraphQL\Base;

class NameSearch extends Base
{
    /**
     * @param array $params
     *  Example: [
     *      'name' => '',
     *      'birthMonthDay' => '',
     *      'gender' => '',
     *      'adult' => 'EXCLUDE_ADULT',
     *      'limit' => 50,
     *      'sortBy' => 'POPULARITY',
     *      'sortOrder' => 'ASC'
     *  ]
     * @return array
     * @throws Exception
     */
    public function search(array $params = []): array
    {
        // Define default values for the parameters
        $defaults = [
            'name' => '',
            'birthMonthDay' => '',
            'gender' => '',
            'adult' => 'EXCLUDE_ADULT',
            'limit' => 50,
            'sortBy' => 'POPULARITY',
            'sortOrder' => 'ASC'
        ];

        // Merge the defaults with the incoming parameters
        $params = array_merge($defaults, $params);

        // Extract the parameters
        $searchTerm = $params['name'];
        $birthMonthDay = $params['birthMonthDay'];
        $gender = strtoupper($params['gender']);
        $adult = strtoupper($params['adult']);

        $limit = (int)$params['limit'];
        $sortBy = strtoupper($params['sortBy']);
        $sortOrder = strtoupper($params['sortOrder']);

        $birthDateConstraint = "";
        if ($birthMonthDay) {
            $birthDateConstraint = "birthDateConstraint: {birthday: \"--$birthMonthDay\"}";
        }

        $results = [];
        $query = <<<EOF
query advancedSearch {
  advancedNameSearch(
    first: $limit,
    sort: {sortBy: $sortBy sortOrder: $sortOrder}
    constraints: {
        nameTextConstraint: {searchTerm: "$searchTerm"}
        $birthDateConstraint
        genderIdentityConstraint: {anyGender: [$gender]}
        explicitContentConstraint: {explicitContentFilter: $adult}
    }
  ) {
    edges {
      node{
        name {
          id
          nameText {
            text
          }
          bio {
            displayableArticle {
              body {
               plaidHtml
              }
            }
          }
          primaryImage {
             url
          }
          primaryProfessions {
            category {
                text
            }
          }
        }
      }
    }
  }
}
EOF;
        $data = $this->graphql->query($query, "advancedSearch");

        $index = 1;
        foreach ($data->advancedNameSearch->edges as $edge) {
            $imdbId = $edge->node->name->id ?? '';
            $name = $edge->node->name->nameText->text ?? '';

            if (empty($imdbId) or empty($name)) {
                continue;
            }

            // Image url
            $imageUrl = null;
            if (isset($edge->node->name->primaryImage->url) and !empty($edge->node->name->primaryImage->url)) {
                $imageUrl = $this->imageUrl($edge->node->name->primaryImage->url);
            }

            // Jobs
            $job = null;
            if (isset($edge->node->name->primaryProfessions)) {
                $jobs = [];
                foreach ($edge->node->name->primaryProfessions as $primaryProfession) {
                    $jobs[] = $primaryProfession->category->text;
                }

                if (!empty($jobs)) {
                    $job = implode(", ", $jobs);
                }
            }

            // Bio
            $bio = null;
            if (isset($edge->node->name->bio->displayableArticle->body->plaidHtml)) {
                $bio = $this->cleanString($edge->node->name->bio->displayableArticle->body->plaidHtml);
            }

            $results[] = [
                'index' => $index,
                'id' => $imdbId,
                'url' => $this->baseUrl . "/name/" . $imdbId,
                'name' => $this->cleanString($name),
                'imageUrl' => $imageUrl,
                'job' => $job,
                'bio' => $bio,
            ];
            $index++;
        }

        return $results;
    }

}

