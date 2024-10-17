<?php

namespace Hooshid\ImdbScraper\GraphQL;

class Base extends Config
{

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var GraphQL
     */
    protected GraphQL $graphql;

    /**
     * @param Config|null $config OPTIONAL override default config
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: $this;
        $this->graphql = new GraphQL($this->config);
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

    protected function imageUrl(string $url = null): ?array
    {
        if (!$url) {
            return null;
        }

        return [
            "original" => $url,
            "small" => @str_replace(".jpg", "UY74_CR41,0,50,74_.jpg", $url),
            "120x120" => @str_replace(".jpg", "UX120_CR0,0,120,120_.jpg", $url),
        ];
    }
}
