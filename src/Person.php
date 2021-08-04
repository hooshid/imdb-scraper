<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Base;
use Hooshid\ImdbScraper\Base\Config;

class Person extends Base
{
    protected $data = [
        'full_name' => null,
        'photo' => [],
        'birth' => [],
        'death' => [],
        'birth_name' => null,
        'nick_names' => [],
        'body_height' => [],
        'bio' => [],
    ];

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
        return "https://" . $this->imdbSiteUrl . "/name/nm" . $this->imdb_id . $this->getUrlSuffix($page);
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
            "name" => "/",
            "bio" => "/bio"
        ];

        if (isset($pageUrls[$pageName])) {
            return $pageUrls[$pageName];
        }

        throw new Exception("Could not find URL for page $pageName");
    }

    /***************************************[ Main Methods ]***************************************/

    /**
     * Set up the URL to the person page
     *
     * @return string
     */
    public function mainUrl(): string
    {
        return "https://" . $this->imdbSiteUrl . "/name/nm" . $this->imdbId() . "/";
    }

    /**
     * this function return full extracted data in single json
     *
     * @return array
     */
    public function full(): array
    {
        $this->fullName();
        $this->photo();
        $this->birth();
        $this->death();
        $this->birthName();
        $this->nickNames();
        $this->bodyHeight();
        $this->bio();

        return $this->data;
    }

    /***************************************[ Main Page ]***************************************/
    /**
     * Get the name of the person
     *
     * @return string|null
     */
    public function fullName(): ?string
    {
        if (empty($this->data['full_name'])) {
            // if bio page loaded, we use this page to extract person name
            if (!empty($this->page['bio'])) {
                if (preg_match("/<title>(.*?) - Biography - IMDb<\/title>/i", $this->getContentOfPage("bio"), $match)) {
                    $this->data['full_name'] = $this->cleanString($match[1]);
                } elseif (preg_match("/<title>IMDb - Biography - (.*?)<\/title>/i", $this->getContentOfPage("bio"), $match)) {
                    $this->data['full_name'] = $this->cleanString($match[1]);
                }
            } else {
                if (preg_match("/<title>(.*?) - IMDb<\/title>/i", $this->getContentOfPage("name"), $match)) {
                    $this->data['full_name'] = $this->cleanString($match[1]);
                } elseif (preg_match("/<title>IMDb - (.*?)<\/title>/i", $this->getContentOfPage("name"), $match)) {
                    $this->data['full_name'] = $this->cleanString($match[1]);
                }
            }
        }

        return $this->data['full_name'];
    }

    /**
     * Get photo
     *
     * @return array
     */
    public function photo(): array
    {
        if (empty($this->data['photo'])) {
            $source = $this->getContentOfPage("name");
            if (preg_match('/<img class="no-pic-image".*alt="No photo available.*>/isU', $source)) {
                return [];
            }

            if (preg_match('!<td.*?id="img_primary".*?>*.*?<img.*?src="(.*?)"!ims', $source, $match)) {
                $arr = explode('@@', $match[1]);
                if (!isset($arr[1])) {
                    $arr = explode('@', $match[1]);
                }

                $this->data['photo'] = [
                    "original" => @str_replace($arr[1], ".jpg", $match[1]),
                    "thumbnail" => @$match[1]
                ];
            } else {
                return [];
            }
        }

        return $this->data['photo'];
    }

    /***************************************[ /bio ]***************************************/
    /**
     * Get birth information
     *
     * @return array
     */
    public function birth(): array
    {
        if (empty($this->data['birth'])) {
            if (preg_match('|Born</td>(.*)</td|iUms', $this->getContentOfPage("bio"), $match)) {
                preg_match('|/search/name\?birth_monthday=(\d+)-(\d+).*?\n?>(.*?) \d+<|', $match[1], $day_mon);
                preg_match('|/search/name\?birth_year=(\d{4})|ims', $match[1], $year);
                preg_match('|/search/name\?birth_place=.*?"\s*>(.*?)<|ims', $match[1], $place);

                if(!empty($day_mon[1]) and !empty($day_mon[2]) and !empty($year[1])) {
                    $date_normalize = mktime(00, 00, 00, $day_mon[1], @$day_mon[2], $year[1]);
                    $full_date = date("Y-m-d", $date_normalize);
                } else {
                    $full_date = null;
                }

                $this->data['birth'] = [
                    "day" => @$day_mon[2],
                    "month" => @$day_mon[3],
                    "mon" => @$day_mon[1],
                    "year" => @$year[1],
                    "date" => @$full_date,
                    "place" => @$this->cleanString($place[1])
                ];
            }
        }

        return $this->data['birth'];
    }

    /**
     * Get death information
     *
     * @return array
     */
    public function death(): array
    {
        if (empty($this->data['death'])) {
            if (preg_match('|Died</td>(.*?)</td|ims', $this->getContentOfPage("bio"), $match)) {
                preg_match('|/search/name\?death_monthday=(\d+)-(\d+).*?\n?>(.*?) \d+<|', $match[1], $day_mon);
                preg_match('|/search/name\?death_date=(\d{4})|ims', $match[1], $year);
                preg_match('|/search/name\?death_place=.*?"\s*>(.*?)<|ims', $match[1], $place);
                preg_match('/\(([^)]+)\)/ims', $match[1], $cause);

                if(!empty($day_mon[1]) and !empty($day_mon[2]) and !empty($year[1])) {
                    $date_normalize = mktime(00, 00, 00, $day_mon[1], @$day_mon[2], $year[1]);
                    $full_date = date("Y-m-d", $date_normalize);
                } else {
                    $full_date = null;
                }

                $this->data['death'] = [
                    "day" => @$day_mon[2],
                    "month" => @$day_mon[3],
                    "mon" => @$day_mon[1],
                    "year" => @$year[1],
                    "date" => @$full_date,
                    "place" => @$this->cleanString($place[1]),
                    "cause" => @$this->cleanString($cause[1])
                ];
            }
        }

        return $this->data['death'];
    }

    /**
     * Get the birth name
     *
     * @return string|null
     */
    public function birthName(): ?string
    {
        if (empty($this->data['birth_name'])) {
            if (preg_match("!Birth Name</td>\s*<td>(.*?)</td>\n!m", $this->getContentOfPage("bio"), $match)) {
                $this->data['birth_name'] = $this->cleanString($match[1]);
            }
        }

        return $this->data['birth_name'];
    }

    /**
     * Get the nick names
     *
     * @return array
     */
    public function nickNames(): array
    {
        if (empty($this->data['nick_names'])) {
            $source = $this->getContentOfPage("bio");
            if (preg_match("!Nicknames</td>\s*<td>\s*(.*?)</td>\s*</tr>!ms", $source, $match)) {
                $nick_names = explode("<br>", $match[1]);
                foreach ($nick_names as $nick_name) {
                    $nick_name = $this->cleanString($nick_name);
                    if (!empty($nick_name)) {
                        $this->data['nick_names'][] = $nick_name;
                    }
                }
            } elseif (preg_match('!Nickname</td><td>\s*([^<]+)\s*</td>!', $source, $match)) {
                $this->data['nick_names'][] = $this->cleanString($match[1]);
            }
        }

        return $this->data['nick_names'];
    }

    /**
     * Get the body height
     *
     * @return array
     */
    public function bodyHeight(): array
    {
        if (empty($this->data['body_height'])) {
            if (preg_match("!Height</td>\s*<td>\s*(?<imperial>.*?)\s*(&nbsp;)?\((?<metric>.*?)\)!m", $this->getContentOfPage("bio"), $match)) {
                $this->data['body_height']["imperial"] = str_replace('&nbsp;', ' ', trim($match['imperial']));
                $this->data['body_height']["metric"] = str_replace('&nbsp;', ' ', trim($match['metric']));

                // change to centimeter
                $height = $this->data['body_height']["metric"];
                $height = str_replace(["m", ".", " "], "", $height);
                if (strlen($height) == '2') {
                    $height = $height . '0';
                }
                $this->data['body_height']["metric_cm"] = (int)$height;
            }

            if (empty($this->data['body_height'])) {
                $dom = $this->getHtmlDomParser("name");

                if ($dom->findOneOrFalse('#details-height') == false) {
                    return [];
                }

                $height = $dom->find('#details-height', 0)->text();
                $height = str_replace('&nbsp;', ' ', trim($height));
                $height = str_replace('Height:', '', trim($height));

                preg_match("!(?<imperial>.*?)\((?<metric>.*?)\)!m", $height, $match);

                if(empty($match['imperial']) or empty($match['metric'])){
                    return [];
                }

                $this->data['body_height']["imperial"] = trim($match['imperial']);
                $this->data['body_height']["metric"] = trim($match['metric']);

                // change to centimeter
                $height = $this->data['body_height']["metric"];
                $height = str_replace(["m", ".", " "], "", $height);
                if (strlen($height) == '2') {
                    $height = $height . '0';
                }
                $this->data['body_height']["metric_cm"] = (int)$height;
            }
        }

        return $this->data['body_height'];
    }

    /**
     * Get the person's mini bio
     *
     * @return array
     */
    public function bio(): array
    {
        if (empty($this->data['bio'])) {
            $this->getContentOfPage("bio");
            // no such page
            if ($this->page["bio"] == "cannot open page") {
                return [];
            }

            if (preg_match('!<h4 class="li_group">Mini Bio[^>]+?>(.+?)<(h4 class="li_group"|div class="article")!ims', $this->page["bio"], $block)) {
                preg_match_all('!<div class="soda.*?\s*<p>\s*(?<bio>.+?)\s</p>\s*<p><em>- IMDb Mini Biography By:\s*(?<author>.+?)\s*</em>!ims', $block[1], $matches);
                for ($i = 0; $i < count($matches[0]); ++$i) {
                    $bio["text"] = str_replace("href=\"/name/nm", "href=\"https://" . $this->imdbSiteUrl . "/name/nm",
                        str_replace("href=\"/title/tt", "href=\"https://" . $this->imdbSiteUrl . "/title/tt",
                            str_replace('/search/name', 'https://' . $this->imdbSiteUrl . '/search/name',
                                $matches['bio'][$i])));
                    $author = 'Written by ' . (str_replace('/search/name',
                            'https://' . $this->imdbSiteUrl . '/search/name', $matches['author'][$i]));
                    if (@preg_match('!href="(.+?)"[^>]*>\s*(.*?)\s*</a>!', $author, $match)) {
                        $bio["author"]["url"] = $match[1];
                        $bio["author"]["name"] = $match[2];
                    } else {
                        $bio["author"]["url"] = '';
                        $bio["author"]["name"] = trim($matches['author'][$i]);
                    }

                    $this->data['bio'][] = $bio;
                }
            }
        }

        return $this->data['bio'];
    }
}

