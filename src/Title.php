<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Title extends Base
{
    protected $data = [
        'title' => null,
        'original_title' => null,
        'type' => null,
        'year' => null,
        'end_year' => null,
        'runtime' => null,
        'photo' => [],
        'tagline' => null,
        'genres' => [],
        'languages' => [],
        'languages_detailed' => [],
        'countries' => [],
        'countries_detailed' => [],
        'rating' => null,
        'votes' => null,
        'colors' => [],
        'sounds' => [],
        'aspect_ratio' => null,
        'taglines' => [],
        'locations' => [],
        'keywords' => [],
        'mpaas' => [],
        'mpaa_reason' => null,
    ];

    protected $jsonLD = null;

    /**
     * @param string $id IMDB ID to use for data retrieval
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct($id, Config $config = null)
    {
        parent::__construct($config);
        $this->setid($id);
    }

    /**
     * Build imdb url
     *
     * @param string|null $page
     * @return string
     * @throws \Exception
     */
    protected function buildUrl($page = null): string
    {
        return "https://" . $this->imdbSiteUrl . "/title/tt" . $this->imdb_id . $this->getUrlSuffix($page);
    }

    /**
     * Get url suffix
     *
     * @param string $pageName
     * @return string
     * @throws \Exception
     */
    protected function getUrlSuffix(string $pageName): string
    {
        $pageUrls = [
            "AlternateVersions" => '/alternateversions',
            "Awards" => "/awards",
            "CompanyCredits" => "/companycredits",
            "CrazyCredits" => "/crazycredits",
            "Credits" => "/fullcredits",
            "Episodes" => "/episodes",
            "ExtReviews" => "/externalreviews",
            "goofs" => "/trivia?tab=gf",
            "keywords" => "/keywords",
            "locations" => "/locations",
            "MovieConnections" => "/movieconnections",
            "OfficialSites" => "/officialsites",
            "parentalguide" => "/parentalguide",
            "Plot" => "/plotsummary",
            "Quotes" => "/quotes",
            "ReleaseInfo" => "/releaseinfo",
            "Soundtrack" => "/soundtrack",
            "Synopsis" => "/plotsummary",
            "taglines" => "/taglines",
            "Technical" => "/technical",
            "title" => "/",
            "Trailers" => "/videogallery/content_type-trailer",
            "Trivia" => "/trivia",
            "VideoSites" => "/externalsites",
        ];

        if (isset($pageUrls[$pageName])) {
            return $pageUrls[$pageName];
        }

        if (preg_match('!^Episodes-(-?\d+)$!', $pageName, $match)) {
            return '/episodes?season=' . $match[1];
        }

        throw new Exception("Could not find URL for page $pageName");
    }

    /***************************************[ Main Methods ]***************************************/

    /**
     * Set up the URL to the title page
     *
     * @return string
     */
    public function mainUrl(): string
    {
        return "https://" . $this->imdbSiteUrl . "/title/tt" . $this->imdbId() . "/";
    }

    /**
     * this function return full extracted data in single json
     *
     * @return array
     */
    public function full(): array
    {
        $this->title();
        $this->originalTitle();
        $this->setupTitleYearType();
        $this->runtime();
        $this->photo();
        $this->tagline();
        $this->genres();
        $this->languages();
        $this->countries();
        $this->rating();
        $this->votes();
        $this->colors();
        $this->sounds();
        $this->aspectRatio();
        $this->taglines();
        $this->locations();
        $this->keywords();
        $this->mpaa();
        $this->mpaaReason();

        return $this->data;
    }

    /**
     * @return mixed|null
     */
    protected function jsonLD()
    {
        if ($this->jsonLD) {
            return $this->jsonLD;
        }
        $page = $this->getContentOfPage('title');
        preg_match('#<script type="application/ld\+json">(.+?)</script>#ims', $page, $matches);
        $this->jsonLD = json_decode($matches[1]);

        return $this->jsonLD;
    }

    /***************************************[ Main Page ]***************************************/

    /**
     * Setup title, year and type properties
     */
    protected function setupTitleYearType()
    {
        $this->getContentOfPage("title");
        if (@preg_match('!<title>(IMDb\s*-\s*)?(?<ititle>.*)(\s*-\s*IMDb)?</title>!', $this->page["title"], $imatch)) {
            $ititle = $imatch['ititle'];
            // serial
            if (preg_match('!(?<title>.*) \((?<movietype>.*)(?<year>\d{4}|\?{4})((&nbsp;|–)(?<endyear>\d{4}|)).*\)(.*)!', $ititle, $match)) {
                $this->data['title'] = htmlspecialchars_decode($match['title']);
                $this->data['year'] = $match['year'];
                $this->data['end_year'] = $match['endyear'] ?: null;
                $this->data['type'] = trim($match['movietype']);
            } elseif (preg_match('!(?<title>.*) \((?<movietype>.*)(?<year>\d{4}|\?{4}).*\)(.*)!', $ititle, $match)) {
                $this->data['title'] = htmlspecialchars_decode($match['title']);
                $this->data['year'] = $match['year'];
                $this->data['end_year'] = $match['year'];
                $this->data['type'] = trim($match['movietype']);
            } // not yet released, but have been given a movietype.
            elseif (preg_match('!(?<title>.*) \((?<movietype>.*)\)(.*)!', $ititle, $match)) {
                $this->data['title'] = htmlspecialchars_decode($match['title']);
                $this->data['year'] = null;
                $this->data['end_year'] = null;
                $this->data['type'] = trim($match['movietype']);
            } // not yet released, so no dates etc.
            elseif (preg_match('!<title>(?<title>.*) - IMDb</title>!', $this->page["title"], $match)) {
                $this->data['title'] = htmlspecialchars_decode($match['title']);
                $this->data['year'] = null;
                $this->data['end_year'] = null;
            }

            if ($this->data['year'] == "????") {
                $this->data['year'] = null;
            }
        }
    }

    /**
     * Get movie title
     *
     * @return string|null
     */
    public function title(): ?string
    {
        if (empty($this->data['title'])) {
            $this->setupTitleYearType();
        }

        if (empty($this->data['title'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="hero-title-block__title"]') == false) {
                return null;
            }

            $this->data['title'] = $dom->find('[data-testid="hero-title-block__title"]', 0)->innerText();
            $this->data['title'] = $this->cleanString(htmlspecialchars_decode($this->data['title']));
        }

        return $this->data['title'];
    }

    /**
     * Get movie original title
     *
     * @return string|null
     */
    public function originalTitle(): ?string
    {
        if (empty($this->data['original_title'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="hero-title-block__original-title"]') == false) {
                return null;
            }

            $this->data['original_title'] = $dom->find('[data-testid="hero-title-block__original-title"]', 0)->innerText();
            $this->data['original_title'] = $this->cleanString(htmlspecialchars_decode($this->data['original_title']), "Original title:");
        }

        return $this->data['original_title'];
    }

    /**
     * Get movie type.
     * it can be returned (Movie, TV Series, TV Episode, TV Special, TV Movie, TV Mini-Series, Video Game, TV Short, Video)
     *
     * @return string
     */
    public function type(): string
    {
        if (empty($this->data['type'])) {
            $this->setupTitleYearType();

            if (!empty($this->data['type'])) {
                return $this->data['type'];
            }

            // TV Special isn't shown in the page title but is mentioned next to the release date
            if (preg_match('/title="See more release dates" >TV Special/', $this->getContentOfPage("title"), $match)) {
                $this->data['type'] = 'TV Special';
            }

            if(empty($this->data['type'])){
                $this->data['type'] = 'Movie';
            }
        }

        return $this->data['type'];
    }

    /**
     * Get year
     *
     * @return int|null
     */
    public function year(): ?int
    {
        if (empty($this->data['year'])) {
            $this->setupTitleYearType();
        }

        return $this->data['year'];
    }

    /**
     * Get end-year
     * Usually this returns the same value as year() -- except for those cases where production spanned multiple years, usually for series
     *
     * @return int|null
     */
    public function endYear(): ?int
    {
        if (empty($this->data['end_year'])) {
            $this->setupTitleYearType();
        }

        return $this->data['end_year'];
    }

    /**
     * Get overall runtime (first one mentioned on title page)
     * runtime in minutes (if set), NULL otherwise
     *
     * @return int|null
     */
    public function runtime(): ?int
    {
        if (empty($this->data['runtime'])) {
            $jsonValue = isset($this->jsonLD()->duration) ? $this->jsonLD()->duration : (isset($this->jsonLD()->timeRequired) ? $this->jsonLD()->timeRequired : null);
            if (isset($jsonValue) && preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $jsonValue, $matches)) {
                $h = isset($matches[1]) ? intval($matches[1]) * 60 : 0;
                $m = isset($matches[2]) ? intval($matches[2]) : 0;
                return $this->data['runtime'] = $h + $m;
            }
        }

        if (empty($this->data['runtime'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="title-techspec_runtime"]') == false) {
                return null;
            }

            $runtimeValue = $dom->find('[data-testid="title-techspec_runtime"] li span', 0)->innerText();
            if (isset($runtimeValue)) {
                // ..h ..min
                if(preg_match('/(\d{1,2})h (\d{1,2})min/', $runtimeValue, $matches)) {
                    $h = isset($matches[1]) ? intval($matches[1]) * 60 : 0;
                    $m = isset($matches[2]) ? intval($matches[2]) : 0;
                    return $this->data['runtime'] = $h + $m;
                } // ..h
                elseif(preg_match('/(\d{1,2})h/', $runtimeValue, $matches)) {
                    $m = isset($matches[1]) ? intval($matches[1]) * 60 : 0;
                    return $this->data['runtime'] = $m;
                } // ..min
                elseif(preg_match('/(\d{1,2})min/', $runtimeValue, $matches)) {
                    $m = isset($matches[1]) ? intval($matches[1]) : 0;
                    return $this->data['runtime'] = $m;
                }
            }
        }

        return $this->data['runtime'];
    }

    /**
     * Get photo poster
     *
     * @return array
     */
    public function photo(): array
    {
        if (empty($this->data['photo'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="hero-media__poster"] img') == false) {
                return [];
            }

            $photo = $dom->find('[data-testid="hero-media__poster"] img', 0)->getAttribute('src');

            $arr = explode('@@', $photo);
            if (!isset($arr[1])) {
                $arr = explode('@', $photo);
            }

            $this->data['photo'] = [
                "original" => @str_replace($arr[1], ".jpg", $photo),
                "thumbnail" => @$photo
            ];
        }

        return $this->data['photo'];
    }

    /**
     * Get the main tagline for the movie
     *
     * @return string|null
     */
    public function tagline(): ?string
    {
        if (empty($this->data['tagline'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="storyline-taglines"]') == false) {
                return null;
            }

            $this->data['tagline'] = $dom->find('[data-testid="storyline-taglines"] span', 0)->innerText();
            $this->data['tagline'] = $this->cleanString($this->data['tagline']);
        }

        return $this->data['tagline'];
    }

    /**
     * Get all genres
     *
     * @return array
     */
    public function genres(): array
    {
        if (empty($this->data['genres'])) {
            $genres = isset($this->jsonLD()->genre) ? $this->jsonLD()->genre : [];
            if (!is_array($genres)) {
                $genres = (array)$genres;
            }
            $this->data['genres'] = $genres;
        }

        if (empty($this->data['genres'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="storyline-genres"]') == false) {
                return [];
            }

            $this->data['genres'] = $dom->find('[data-testid="storyline-genres"] a')->text();
        }

        return $this->data['genres'];
    }

    /**
     * Get all languages
     *
     * @return array
     */
    public function languages(): array
    {
        if (empty($this->data['languages'])) {
            if (preg_match_all('!href="/search/title\?.+?primary_language=([^&]*)[^>]*>\s*(.*?)\s*</a>(\s+\((.*?)\)|)!m',
                $this->getContentOfPage("title"), $matches)) {
                $this->data['languages'] = $matches[2];
                $mc = count($matches[2]);
                for ($i = 0; $i < $mc; $i++) {
                    $this->data['languages_detailed'][] = [
                        'name' => $matches[2][$i],
                        'code' => $matches[1][$i],
                        'comment' => trim($matches[4][$i])
                    ];
                }
            }
        }

        return $this->data['languages'];
    }

    /**
     * Get all languages with details
     *
     * @return array
     */
    public function languagesDetailed(): array
    {
        if (empty($this->data['languages_detailed'])) {
            $this->languages();
        }

        return $this->data['languages_detailed'];
    }

    /**
     * Get country of production
     *
     * @return array
     */
    public function countries(): array
    {
        if (empty($this->data['countries'])) {
            if (preg_match_all('!href="/search/title.+?country_of_origin=([^&]*)[^>]*>(.*?)<!m',
                $this->getContentOfPage("title"), $matches)) {
                $this->data['countries'] = $matches[2];
                $mc = count($matches[2]);
                for ($i = 0; $i < $mc; $i++) {
                    $this->data['countries_detailed'][] = [
                        'name' => $this->cleanString($matches[2][$i]),
                        'code' => $this->cleanString(strtolower($matches[1][$i]))
                    ];
                }
            }
        }

        return $this->data['countries'];
    }

    /**
     * Get all countries with details
     *
     * @return array
     */
    public function countriesDetailed(): array
    {
        if (empty($this->data['countries_detailed'])) {
            $this->countries();
        }

        return $this->data['countries_detailed'];
    }

    /**
     * Get movie rating
     *
     * @return string|null
     */
    public function rating(): ?string
    {
        return $this->data['rating'] = isset($this->jsonLD()->aggregateRating->ratingValue) ? (string)$this->jsonLD()->aggregateRating->ratingValue : null;
    }

    /**
     * Return number of votes for this movie
     *
     * @return int|null
     */
    public function votes(): ?int
    {
        return $this->data['votes'] = isset($this->jsonLD()->aggregateRating->ratingCount) ? (int)$this->jsonLD()->aggregateRating->ratingCount : null;
    }

    /**
     * Get the colors this movie was shot in.
     * e.g. Color, Black and White
     *
     * @return array
     */
    public function colors(): array
    {
        if (empty($this->data['colors'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="title-techspec_color"]') == false) {
                return [];
            }

            $this->data['colors'] = $dom->find('[data-testid="title-techspec_color"] a')->text();
        }

        return $this->data['colors'];
    }

    /**
     * Get sound formats
     *
     * @return array
     */
    public function sounds(): array
    {
        if (empty($this->data['sounds'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="title-techspec_soundmix"]') == false) {
                return [];
            }

            $this->data['sounds'] = $dom->find('[data-testid="title-techspec_soundmix"] a')->text();
        }

        return $this->data['sounds'];
    }

    /**
     * Aspect Ratio of movie screen
     *
     * @return string|null
     */
    public function aspectRatio(): ?string
    {
        if (empty($this->data['aspect_ratio'])) {
            $dom = $this->getHtmlDomParser("title");

            if ($dom->findOneOrFalse('[data-testid="title-techspec_aspectratio"]') == false) {
                return null;
            }

            $this->data['aspect_ratio'] = $dom->find('[data-testid="title-techspec_aspectratio"] li span', 0)->innerText();
            $this->data['aspect_ratio'] = $this->cleanString($this->data['aspect_ratio']);
        }

        return $this->data['aspect_ratio'];
    }

    /***************************************[ /taglines ]***************************************/
    /**
     * Get all available taglines for the movie
     *
     * @return array
     */
    public function taglines(): array
    {
        if (empty($this->data['taglines'])) {
            $this->getContentOfPage("taglines");
            // no such page
            if ($this->page["taglines"] == "cannot open page") {
                return [];
            }

            if (preg_match_all('!<div class="soda[^>]+>\s*(.*)\s*</div!U', $this->page["taglines"], $matches)) {
                $this->data['taglines'] = array_map('trim', $matches[1]);
            }
        }

        return $this->data['taglines'];
    }

    /***************************************[ /locations ]***************************************/
    /**
     * Filming locations
     *
     * @return array
     */
    public function locations(): array
    {
        if (empty($this->data['locations'])) {
            $dom = $this->getHtmlDomParser("locations");

            // no such page
            if ($dom->findOneOrFalse('#filming_locations .soda dt a') == false) {
                return [];
            }

            foreach ($dom->find('#filming_locations dt a')->text() as $location) {
                $this->data['locations'][] = $this->cleanString($location);
            }
        }

        return $this->data['locations'];
    }

    /***************************************[ /keywords ]***************************************/
    /**
     * Get the complete keywords for the movie
     *
     * @return array
     */
    public function keywords(): array
    {
        if (empty($this->data['keywords'])) {
            $page = $this->getContentOfPage("keywords");
            if (preg_match_all('|<a href="/search/keyword[^>]+?>(.*?)</a>|', $page, $matches)) {
                $this->data['keywords'] = $matches[1];
            }
        }

        return $this->data['keywords'];
    }

    /***************************************[ /parentalguide ]***************************************/
    /**
     * Get the MPAA rating / Parental Guidance / Age rating for this title by country
     *
     * @return array [country => rating]
     */
    public function mpaa(): array
    {
        if (empty($this->data['mpaas'])) {
            $source = $this->getContentOfPage("parentalguide");
            if (preg_match_all("|/search/title\?certificates=.*?>\s*(.*?):(.*?)<|", $source, $matches)) {
                $cc = count($matches[0]);
                for ($i = 0; $i < $cc; ++$i) {
                    $this->data['mpaas'][$matches[1][$i]] = $this->cleanString($matches[2][$i]);
                }
            }
        }

        return $this->data['mpaas'];
    }

    /**
     * Find out the reason for the MPAA rating
     *
     * @return string|null
     */
    public function mpaaReason(): ?string
    {
        if (empty($this->data['mpaa_reason'])) {
            $source = $this->getContentOfPage("parentalguide");
            if (preg_match('!id="mpaa-rating"\s*>\s*<td[^>]*>.*</td>\s*<td[^>]*>(.*)</td>!im', $source, $match)) {
                $this->data['mpaa_reason'] = trim($match[1]);
            }
        }

        return $this->data['mpaa_reason'];
    }

}
