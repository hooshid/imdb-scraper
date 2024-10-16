<?php

namespace Hooshid\ImdbScraper\GraphQL;

use Exception;
use stdClass;

class GraphQL
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * GraphQL constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws Exception
     */
    public function query($query, $qn = null, $variables = array()): stdClass
    {
        // strip spaces from query
        $fullQuery = implode("\n", array_map('trim', explode("\n", $query)));
        return $this->doRequest($fullQuery, $qn, $variables);
    }

    /**
     * @param string $query
     * @param string|null $queryName
     * @param array $variables
     * @return stdClass
     * @throws Exception
     */
    private function doRequest(string $query, string $queryName = null, array $variables = []): stdClass
    {
        $request = new Request('https://api.graphql.imdb.com/');
        $request->addHeaderLine("Content-Type", "application/json");
        if ($this->config->useLocalization === true) {
            if (!empty($this->config->country)) {
                $request->addHeaderLine("X-Imdb-User-Country", $this->config->country);
            }
            if (!empty($this->config->language)) {
                $request->addHeaderLine("X-Imdb-User-Language", $this->config->language);
            }
        }
        $payload = json_encode(
            [
                'operationName' => $queryName,
                'query' => $query,
                'variables' => $variables
            ]
        );

        $request->post($payload);
        if ($request->getStatus() == 200) {
            return json_decode($request->getResponseBody())->data;
        } else {
            /*
            $this->logger->error(
                "[GraphQL] Failed to retrieve query [{queryName}]. Response headers:{headers}. Response body:{body}",
                array('queryName' => $queryName, 'headers' => $request->getLastResponseHeaders(), 'body' => $request->getResponseBody())
            );
            */
            $errorId = $variables['id'];
            throw new Exception("Failed to retrieve query [$queryName] , IMDb id [$errorId]");
        }
    }
}
