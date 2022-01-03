<?php

namespace Hooshid\ImdbScraper\Base;

use voku\helper\HtmlDomParser;

class Base extends Config
{

    protected $months = array(
        "January" => "01",
        "Jan" => "01",
        "February" => "02",
        "Feb" => "02",
        "March" => "03",
        "Mar" => "03",
        "April" => "04",
        "Apr" => "04",
        "May" => "05",
        "June" => "06",
        "Jun" => "06",
        "July" => "07",
        "Jul" => "07",
        "August" => "08",
        "Aug" => "08",
        "September" => "09",
        "Sep" => "09",
        "October" => "10",
        "Oct" => "10",
        "November" => "11",
        "Nov" => "11",
        "December" => "12",
        "Dec" => "12"
    );

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Pages
     */
    protected $pages;

    protected $page = array();

    protected $htmlDomParser = [];

    /**
     * @var string 7 digit identifier for this person
     */
    protected $imdb_id;

    /**
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(Config $config = null)
    {
        parent::__construct();

        if ($config) {
            foreach (array(
                         "language",
                         "imdbSiteUrl",
                         "cacheDir",
                         "useCache",
                         "storeCache",
                         "useZip",
                         "convertToZip",
                         "cacheExpire",
                         "debug",
                         "throwHttpExceptions",
                         "useProxy",
                         "ipAddress",
                         "proxyHost",
                         "proxyPort",
                         "proxyUser",
                         "proxyPassword",
                         "defaultAgent",
                         "forceAgent"
                     ) as $key) {
                $this->$key = $config->$key;
            }
        }

        $this->config = $config ?: $this;
        $this->pages = new Pages($this->config);
    }

    /**
     * Retrieve the IMDB ID
     * @return string id IMDBID currently used
     */
    public function imdbId(): string
    {
        return $this->imdb_id;
    }

    /**
     * Set and validate the IMDb ID
     * @param string id IMDb ID
     */
    protected function setid($id)
    {
        if (is_numeric($id)) {
            $this->imdb_id = str_pad($id, 7, '0', STR_PAD_LEFT);
        } elseif (preg_match("/(?:nm|tt)(\d{7,8})/", $id, $matches)) {
            $this->imdb_id = $matches[1];
        }
    }

    /**
     * Get and validate the IMDB ID
     *
     * @param string
     * @return mixed|string|null
     */
    public function getImdbId($id)
    {
        if (empty($id)) {
            return null;
        }

        $imdbId = null;
        if (preg_match("/(tt\d{5,8})/", $id, $matches)) {
            $imdbId = $matches[1];
        }

        return $imdbId;
    }


    /**
     * Get numerical value for month name
     * @param string name name of month
     * @return integer month number
     */
    protected function monthNo($mon)
    {
        return @$this->months[$mon];
    }

    /**
     * Get a page from IMDb, which will be cached in memory for repeated use
     * @param string $page Name of the page or some other context to build the URL with to retrieve the page
     * @return string
     */
    protected function getContentOfPages($page = null): string
    {
        return $this->pages->get($this->buildUrl($page));
    }

    /**
     * Get page content
     *
     * @param string|null $page
     * @return string
     */
    protected function getContentOfPage($page = null): string
    {
        if (!empty($this->page[$page])) {
            return $this->page[$page];
        }

        $this->page[$page] = $this->pages->get($this->buildUrl($page));

        return $this->page[$page];
    }
    /**
     * Overrideable method to build the URL used by getPage
     * @param string $page OPTIONAL
     * @return string
     */
    protected function buildUrl($page = null): string
    {
        return '';
    }

    /**
     * @param $page
     * @return mixed|HtmlDomParser
     */
    protected function getHtmlDomParser($page){
        if (!empty($this->htmlDomParser[$page])) {
            return $this->htmlDomParser[$page];
        }

        $source = $this->getContentOfPage($page);
        return $this->htmlDomParser[$page] = HtmlDomParser::str_get_html($source);
    }

    protected function cleanString($str, $remove = null): ?string
    {
        if (!empty($remove)) {
            $str = str_replace($remove, "", $str);
        }

        $str = str_replace("&amp;", "&", $str);
        $str = str_replace("&nbsp;", " ", $str);
        $str = html_entity_decode($str);

        return ($str ? trim(strip_tags($str)) : null);
    }
}
