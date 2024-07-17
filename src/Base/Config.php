<?php

namespace Hooshid\ImdbScraper\Base;

class Config
{

    /**
     * Set the language Imdb will use for titles, and some other localised data (e.g. tv episode air dates)
     * Any valid language code can be used here (e.g. en-US, de, pt-BR).
     * If this option is specified, an Accept-Language header with this value
     * will be included in requests to IMDb.
     * @var string
     */
    public $language = "";

    /**
     * IMDB base domain.
     * @var string
     */
    protected $baseUrl = 'https://www.imdb.com';

    /**
     * IMDB domain to use.
     * @var string
     */
    public $imdbSiteUrl = "www.imdb.com";

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
    public $defaultAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

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
