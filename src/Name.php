<?php

namespace Hooshid\ImdbScraper;

use Exception;
use Hooshid\ImdbScraper\Base\Old\Base;
use Hooshid\ImdbScraper\Base\Config;

class Name extends Base
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
    protected function buildUrl(string $page = null): string
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
            "bio" => "/bio/"
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
            $dom = $this->getHtmlDomParser("name");

            if ($dom->findOneOrFalse('.ipc-page-background--baseAlt .ipc-page-section--baseAlt .ipc-poster .ipc-poster__poster-image img.ipc-image') == false) {
                return [];
            }

            $photo = $dom->find('.ipc-page-background--baseAlt .ipc-page-section--baseAlt .ipc-poster .ipc-poster__poster-image img.ipc-image', 0)->getAttribute('src');

            $this->data['photo'] = $this->photoUrl($photo);
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
        // new theme
        if (empty($this->data['birth'])) {
            $dom = $this->getHtmlDomParser("name");

            // check data exist in index page of name
            if ($dom->findOneOrFalse('[data-testid="nm_pd_bl"]') != false) {

                $html = $dom->find('[data-testid="nm_pd_bl"] ul', 0)->innerhtml;

                preg_match('/\/search\/name\/\?birth_monthday=(\d+)-(\d+).*?\n?>(.*?) \d+<\/a>/im', $html, $day_mon);
                preg_match('/\/search\/name\/\?birth_year=(\d{4})/im', $html, $year);
                preg_match('/\/search\/name\/\?birth_place=.*?"\s*>(.*?)<\/a>/im', $html, $place);

                if (!empty($day_mon[1]) and !empty($day_mon[2]) and !empty($year[1])) {
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

        // old theme
        if (empty($this->data['birth'])) {
            if (preg_match('|Born</td>(.*)</td|iUms', $this->getContentOfPage("bio"), $match)) {
                preg_match('|/search/name\?birth_monthday=(\d+)-(\d+).*?\n?>(.*?) \d+<|', $match[1], $day_mon);
                preg_match('|/search/name\?birth_year=(\d{4})|ims', $match[1], $year);
                preg_match('|/search/name\?birth_place=.*?"\s*>(.*?)<|ims', $match[1], $place);

                if (!empty($day_mon[1]) and !empty($day_mon[2]) and !empty($year[1])) {
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
            $dom = $this->getHtmlDomParser("name");

            // check data exist in index page of name
            if ($dom->findOneOrFalse('[data-testid="nm_pd_dl"]') != false) {
                $html = $dom->find('[data-testid="nm_pd_dl"] ul', 0)->innerhtml;

                // date of death
                preg_match('/\/search\/name\/\?death_date=(\d{4}-\d{1,2}-\d{1,2}).*"\n?>(\w+)\s(\d+)<\/a>/im', $html, $death_date_regx);
                if (!empty($death_date_regx)) {
                    $death_date = explode("-", $death_date_regx[1]);
                    // place of death
                    preg_match('/\/search\/name\/\?death_place=.*?"\s*>(.*?)<\/a>/im', $html, $place);
                    // cause of death
                    preg_match('/\(([^)]+)\)/im', $html, $cause);

                    if (!empty($death_date[0]) and !empty($death_date[1]) and !empty($death_date[2])) {
                        $date_normalize = mktime(00, 00, 00, $death_date[1], @$death_date[2], $death_date[0]);
                        $full_date = date("Y-m-d", $date_normalize);
                    } else {
                        $full_date = null;
                    }

                    $this->data['death'] = [
                        "day" => @$death_date[2],
                        "month" => @$death_date_regx[2],
                        "mon" => @$death_date[1],
                        "year" => @$death_date[0],
                        "date" => @$full_date,
                        "place" => @$this->cleanString($place[1]),
                        "cause" => @$this->cleanString($cause[1])
                    ];
                }
            }
        }

        if (empty($this->data['death'])) {
            if (preg_match('|Died</td>(.*?)</td|ims', $this->getContentOfPage("bio"), $match)) {
                preg_match('<time datetime="(.*)">', $match[1], $death_date_regx);
                $death_date = explode("-", $death_date_regx[1]);

                preg_match('/<a href="\/search\/name\?death_date=\d{4}-\d{1,2}-\d{1,2}.*"\n?>(\w+)\s(\d+)/', $match[1], $day_mon);

                preg_match('|/search/name\?death_place=.*?"\s*>(.*?)<|ims', $match[1], $place); // place of death
                preg_match('/\(([^)]+)\)/ims', $match[1], $cause); // cause of death

                if (!empty($death_date[0]) and !empty($death_date[1]) and !empty($death_date[2])) {
                    $date_normalize = mktime(00, 00, 00, $death_date[1], @$death_date[2], $death_date[0]);
                    $full_date = date("Y-m-d", $date_normalize);
                } else {
                    $full_date = null;
                }

                $this->data['death'] = [
                    "day" => @$death_date[2],
                    "month" => @$day_mon[1],
                    "mon" => @$death_date[1],
                    "year" => @$death_date[0],
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
        // new theme
        if (empty($this->data['birth_name'])) {
            $dom = $this->getHtmlDomParser("bio");

            // check exist in bio page
            if ($dom->findOneOrFalse('[data-testid="sub-section-overview"]') != false) {
                foreach ($dom->find('[data-testid="sub-section-overview"] ul li') as $row) {
                    $label = $this->cleanString($row->find('.ipc-metadata-list-item__label', 0)->innerText());
                    if ($label == "Birth name") {
                        $this->data['birth_name'] = $this->cleanString($row->find('.ipc-metadata-list-item__content-container .ipc-html-content-inner-div', 0)->text());
                    }
                }
            }
        }

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
        // new theme
        if (empty($this->data['nick_names'])) {
            $dom = $this->getHtmlDomParser("name");

            // check exist in index page
            if ($dom->findOneOrFalse('[data-testid="DidYouKnow"]') != false) {

                foreach ($dom->find('[data-testid="DidYouKnow"] div') as $row) {
                    $label = $this->cleanString($row->find('.ipc-metadata-list-item__label', 0)->innerText());
                    if ($label == "Nicknames" or $label == "Nickname") {
                        foreach ($row->find('.ipc-metadata-list-item__content-container ul li span')->text() as $nick_name) {
                            $nick_name = $this->cleanString($nick_name);
                            if (!empty($nick_name)) {
                                $this->data['nick_names'][] = $nick_name;
                            }
                        }
                    }
                }
            }
        }

        // old theme
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
        // new theme
        if (empty($this->data['body_height'])) {
            $dom = $this->getHtmlDomParser("name");

            // check exist in index page
            if ($dom->findOneOrFalse('[data-testid="nm_pd_he"]') != false) {
                $html = $dom->find('[data-testid="nm_pd_he"] .ipc-metadata-list-item__content-container span', 0)->innerText();
                preg_match("/(?<imperial>.*?)\((?<metric>.*?)\)/im", $html, $match);

                if (empty($match['imperial']) or empty($match['metric'])) {
                    return [];
                }

                $imperial = str_replace("″", '"', $match['imperial']);
                $imperial = str_replace("′", "'", $imperial);
                $this->data['body_height']["imperial"] = trim($imperial);
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

                if (empty($match['imperial']) or empty($match['metric'])) {
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
        // new theme
        if (empty($this->data['bio'])) {
            $dom = $this->getHtmlDomParser("bio");

            // check exist
            if ($dom->findOneOrFalse('[data-testid="sub-section-mini_bio"]') != false) {
                $text = $dom->find('[data-testid="sub-section-mini_bio"] .ipc-metadata-list-item__content-container .ipc-html-content-inner-div', 0)->innerText();
                $text = str_replace("class=\"ipc-md-link ipc-md-link--entity\"", "", $text);
                $text = str_replace("/?ref_=nmbio_mbio", "", $text);
                $text = str_replace("?ref_=nmbio_mbio", "", $text);
                $bio["text"] = str_replace("href=\"/name/nm", "href=\"https://" . $this->imdbSiteUrl . "/name/nm",
                    str_replace("href=\"/title/tt", "href=\"https://" . $this->imdbSiteUrl . "/title/tt",
                        str_replace('/search/name', 'https://' . $this->imdbSiteUrl . '/search/name',
                            $text)));

                $author = $dom->find('[data-testid="sub-section-mini_bio"] .ipc-metadata-list-item-html-item--subtext div', 0)->innerText();
                $author = str_replace("- IMDb Mini Biography By:", "", $author);
                if ($author) {
                    $bio["author"]["url"] = '';
                    $bio["author"]["name"] = trim($author);
                } else {
                    $bio["author"]["url"] = null;
                    $bio["author"]["name"] = null;
                }

                $this->data['bio'][] = $bio;
            }
        }

        // old theme
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

