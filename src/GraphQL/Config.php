<?php

namespace Hooshid\ImdbScraper\GraphQL;

class Config
{
    /**
     * @var boolean useLocalization set true to use localization
     * leave this to false if you want US American English
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
    public string $country = "DE";

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
    public string $language = "de-DE";

    /**
     * IMDB base domain.
     * @var string
     */
    protected string $baseUrl = 'https://www.imdb.com';

}
