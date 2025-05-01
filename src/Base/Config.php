<?php

namespace Hooshid\ImdbScraper\Base;

/**
 * Configuration class for IMDb Scraper
 *
 * Handles all configuration options including localization,
 * country/language settings, and base URLs.
 */
class Config
{
    /**
     * Whether to use localization settings
     *
     * @var bool Set to true to use localization, false for US English
     */
    public bool $useLocalization = false;

    /**
     * @var string country set country code
     * possible values:
     * CA (Canada)
     * FR (France)
     * DE (Germany)
     * IN (Indonesia)
     * IT (Italy)
     * BR (Brazil)
     * ES (Spain)
     * MX (Mexico)
     */
    public string $country = "US";

    /**
     * @var string language set language code
     * possible values:
     * fr-CA (French Canada)
     * fr-FR (French France)
     * de-DE (German Germany)
     * hi-IN (hindi Indonesia)
     * it-IT (Italian Italy)
     * pt-BR (Portugues Brazil)
     * es-ES (Spanisch Spain)
     * es-MX (Spanisch Mexico)
     */
    public string $language = "en-US";

    /**
     * IMDB domain to use.
     *
     * @var string
     */
    protected string $imdbSiteUrl = "www.imdb.com";

    /**
     * Get the IMDB site URL
     *
     * @return string
     */
    public function getImdbSiteUrl(): string
    {
        return $this->imdbSiteUrl;
    }

    /**
     * Get the base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return "https://" . $this->imdbSiteUrl;
    }
}
