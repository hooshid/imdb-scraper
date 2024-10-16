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

    protected function imageUrl(string $url = null): ?array
    {
        if (!$url) {
            return null;
        }

        return [
            "original" => $url,
            "small" => @str_replace(".jpg", "QL75_UY74_CR41,0,50,74_.jpg", $url),
        ];
    }
}
