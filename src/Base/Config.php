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
}
