<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;

class Company extends Base
{
    /**
     * Obtains detailed information about a company
     *
     * This info is only available for imdbPro users but through GraphQL it is freely available!
     *
     * @param string $companyId IMDb company ID (e.g. "co0144901" for Netflix)
     * @return array{
     *     id: string,
     *     name: string,
     *     rank: array{
     *         current_rank: int|null,
     *         change_direction: string|null,
     *         difference: int|null
     *     },
     *     country: string,
     *     types: string[],
     *     staff: array<int, array{
     *         id: string,
     *         name: string,
     *         employments: array<int, array{
     *             employment_title: string,
     *             occupation: string,
     *             branch: string|null
     *         }>
     *     }>,
     *     known_for: array<int, array{
     *         id: string,
     *         title: string,
     *         jobs: array<int, array{
     *             category: string,
     *             job: string
     *         }>,
     *         countries: string[],
     *         year: int|null,
     *         end_year: int|null
     *     }>,
     *     affiliations: array<int, array{
     *         id: string,
     *         name: string,
     *         description: string
     *     }>
     * }|array{} Return format:
     *     - Non-empty: Detailed company information including:
     *         - Basic info (id, name, country, types)
     *         - Ranking data (current rank and changes)
     *         - Staff members with their employment history
     *         - Known-for titles with production details
     *         - Company affiliations/relationships
     *     - Empty array if company not found or invalid ID
     * @throws Exception If GraphQL query fails
     */
    public function getInfo(string $companyId): array
    {
        $companyId = trim($companyId);
        if (empty($companyId)) {
            return [];
        }

        $query = <<<GRAPHQL
query Company {
  company(id: "$companyId") {
    id
    companyText {
      text
    }
    country {
      text
    }
    companyTypes {
      text
    }
    meterRanking {
      currentRank
      rankChange {
        changeDirection
        difference
      }
    }
    keyStaff(first: 500) {
      edges {
        node {
          name {
            id
            nameText {
              text
            }
          }
          summary {
            employment(limit: 100) {
              title {
                text
              }
              branch {
                text
              }
              occupation {
                text
              }
            }
          }
        }
      }
    }
    knownForTitles(first: 10) {
      edges {
        node {
          title {
            id
            titleText {
              text
            }
          }
          summary {
            countries {
              text
            }
            jobs {
              category {
                text
              }
              job {
                text
              }
            }
            yearRange {
              year
              endYear
            }
          }
        }
      }
    }
    affiliations(first: 9999) {
      edges {
        node {
          company {
            id
            companyText {
              text
            }
          }
          text
        }
      }
    }
  }
}
GRAPHQL;
        $data = $this->graphql->query($query, "Company");

        if (!isset($data->company) || empty($data->company->id) || empty($data->company->companyText->text)) {
            return [];
        }

        // Company Types
        $types = [];
        if (isset($data->company->companyTypes)) {
            foreach ($data->company->companyTypes as $companyType) {
                $types[] = $companyType->text;
            }
        }

        // Staff
        $staff = [];
        if (!empty($data->company->keyStaff->edges)) {
            foreach ($data->company->keyStaff->edges as $keyStaff) {
                if (empty($keyStaff->node->name->id) || empty($keyStaff->node->name->nameText->text)) {
                    continue;
                }

                // Employments
                $employments = [];
                if (!empty($keyStaff->node->summary->employment)) {
                    foreach ($keyStaff->node->summary->employment as $list) {
                        if (empty($list->title->text) || empty($list->occupation->text)) {
                            continue;
                        }

                        $employments[] = [
                            'employment_title' => $list->title->text,
                            'occupation' => $list->occupation->text,
                            'branch' => $list->branch->text ?? null
                        ];
                    }
                }
                $staff[] = [
                    'id' => $keyStaff->node->name->id,
                    'name' => $keyStaff->node->name->nameText->text,
                    'employments' => $employments
                ];
            }
        }

        // KnownFor
        $knownFor = [];
        if (!empty($data->company->knownForTitles->edges)) {
            foreach ($data->company->knownForTitles->edges as $title) {
                if (empty($title->node->title->id) || empty($title->node->title->titleText->text)) {
                    continue;
                }

                // Jobs
                $jobs = [];
                if (!empty($title->node->summary->jobs)) {
                    foreach ($title->node->summary->jobs as $job) {
                        if (empty($job->category->text) || empty($job->job->text)) {
                            continue;
                        }

                        $jobs[] = [
                            'category' => $job->category->text,
                            'job' => $job->job->text
                        ];
                    }
                }

                // Countries
                $countries = [];
                if (!empty($title->node->summary->countries)) {
                    foreach ($title->node->summary->countries as $country) {
                        if (!empty($country->text)) {
                            $countries[] = $country->text;
                        }
                    }
                }

                $knownFor[] = [
                    'id' => $title->node->title->id,
                    'title' => $title->node->title->titleText->text,
                    'jobs' => $jobs,
                    'countries' => $countries,
                    'year' => $title->node->summary->yearRange->year ?? null,
                    'end_year' => $title->node->summary->yearRange->endYear ?? null
                ];
            }
        }

        // Affiliations
        $affiliations = [];
        if (!empty($data->company->affiliations->edges)) {
            foreach ($data->company->affiliations->edges as $affiliation) {
                if (empty($affiliation->node->company->id) || empty($affiliation->node->company->companyText->text) || empty($affiliation->node->text)) {
                    continue;
                }

                $affiliations[] = [
                    'id' => $affiliation->node->company->id,
                    'name' => $affiliation->node->company->companyText->text,
                    'description' => $affiliation->node->text
                ];
            }
        }

        return [
            'id' => $data->company->id,
            'name' => $data->company->companyText->text,
            'rank' => [
                'current_rank' => $data->company->meterRanking->currentRank ?? null,
                'change_direction' => $data->company->meterRanking->rankChange->changeDirection ?? null,
                'difference' => $data->company->meterRanking->rankChange->difference ?? null,
            ],
            'country' => $data->company->country->text,
            'types' => $types,
            'staff' => $staff,
            'known_for' => $knownFor,
            'affiliations' => $affiliations,
        ];
    }
}

