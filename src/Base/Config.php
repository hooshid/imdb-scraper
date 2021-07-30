<?php

namespace Hooshid\ImdbScraper\Base;

class Config
{

    /**
     * Set the language Imdb will use for titles, and some other localised data (e.g. tv episode air dates)
     * Any valid language code can be used here (e.g. en-US, de, pt-BR).
     * If this option is specified, a Accept-Language header with this value
     * will be included in requests to IMDb.
     * @var string
     */
    public $language = "";

    /**
     * IMDB domain to use.
     * @var string
     */
    public $imdbSiteUrl = "www.imdb.com";

    /**
     * Directory to store cached pages. This must be writable by the web
     * server. It doesn't need to be under documentroot.
     * @var string
     */
    public $cacheDir = './cache/';

    /**
     * Use cached pages if available?
     * @var boolean
     */
    public $useCache = true;

    /**
     * Store the pages retrieved for later use?
     * @var boolean
     */
    public $storeCache = true;

    /**
     * Use zip compression for caching the retrieved html-files?
     * @see $converttozip if you're changing from false to true
     * @var boolean
     */
    public $useZip = true;

    /**
     * Convert non-zip cache-files to zip
     * You might want to use this if you weren't gzipping your cache files, but now are. They will be rewritten when they're used
     * @var boolean
     */
    public $convertToZip = false;

    /**
     * Cache expiration time - cached pages older than this value (in seconds) will
     * be automatically deleted.
     * If 0 cached pages will never expire
     * @var integer
     */
    public $cacheExpire = 604800;

    /**
     * Enable debug mode?
     * @var boolean
     */
    public $debug = false;

    /**
     * Throw exceptions when a request to fetch some content fails?
     * @var boolean
     */
    public $throwHttpExceptions = true;

    #--------------------------------------------------=[ TWEAKING OPTIONS ]=--

    /**
     * Enable HTTP-Proxy support
     * @var bool
     */
    public $useProxy = false;

    /**
     * Set originating IP address of a client connecting to a web server through an HTTP proxy or a load balancer.
     * Useful with language for times when Imdb uses your ip address geo-location before Accept-Language header.
     * If this option is specified, a X-Forwarded-For header with this value will be included in requests to IMDb.
     * @var string
     */
    public $ipAddress = '';

    /**
     * Set hostname of HTTP-Proxy
     * @var string
     */
    public $proxyHost = null;

    /**
     * Set port on which HTTP-Proxy is listening
     * @var int
     */
    public $proxyPort = null;

    /**
     * Set username for authentication against HTTP-Proxy, if the proxy requires login.
     * Only basic authentication is supported.
     * Otherwise leave at default value
     * @var string
     */
    public $proxyUser = null;

    /**
     * Set password for authentication against HTTP-Proxy, if the proxy requires login.
     * Otherwise leave at default value
     * @var string
     */
    public $proxyPassword = '';

    /**
     * Set the default user agent (if none is detected)
     * @var string
     */
    public $defaultAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0';

    /**
     * Enforce the use of a special user agent
     * @var string
     */
    public $forceAgent = '';

    /**
     * Constructor
     * @param string $iniFile *optional* Path to a config file containing any config overrides
     */
    public function __construct($iniFile = null)
    {

    }

}
