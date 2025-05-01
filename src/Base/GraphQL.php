<?php

namespace Hooshid\ImdbScraper\Base;

use Exception;
use stdClass;

/**
 * Client for accessing IMDb data through GraphQL API
 *
 * Handles all GraphQL requests to the IMDb API with support for:
 * - Custom queries
 * - Variables
 * - Localization headers
 * - Error handling
 */
class GraphQL
{
    /** @var Config Configuration object */
    private Config $config;

    /** @var string IMDb GraphQL API endpoint */
    private const API_ENDPOINT = 'https://api.graphql.imdb.com/';

    /**
     * Constructor
     *
     * @param Config $config Configuration object
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Execute a GraphQL query
     *
     * @param string $query The GraphQL query string
     * @param string|null $queryName Name of the query (operation name)
     * @param array $variables Variables to pass with the query
     * @return stdClass Response data
     * @throws Exception On request failure or invalid response
     */
    public function query(string $query, ?string $queryName = null, array $variables = []): stdClass
    {
        $normalizedQuery = $this->normalizeQuery($query);
        return $this->doRequest($normalizedQuery, $queryName, $variables);
    }

    /**
     * Normalize GraphQL query by removing unnecessary whitespace
     *
     * @param string $query Raw query string
     * @return string Normalized query
     */
    private function normalizeQuery(string $query): string
    {
        return implode("\n", array_map('trim', explode("\n", $query)));
    }

    /**
     * Execute the GraphQL request
     *
     * @param string $query Normalized GraphQL query
     * @param string|null $queryName Name of the query
     * @param array $variables Query variables
     * @return stdClass Response data
     * @throws Exception On request failure
     */
    private function doRequest(string $query, ?string $queryName = null, array $variables = []): stdClass
    {
        $request = new Request(self::API_ENDPOINT);
        $this->configureRequestHeaders($request);

        $payload = $this->buildPayload($query, $queryName, $variables);

        if (!$request->post($payload)) {
            throw $this->createRequestException($queryName, $variables, 'Request failed');
        }

        return $this->handleResponse($request, $queryName, $variables);
    }

    /**
     * Configure request headers including localization if enabled
     *
     * @param Request $request Request object to configure
     */
    private function configureRequestHeaders(Request $request): void
    {
        $request->addHeaderLine("Content-Type", "application/json");

        if ($this->config->useLocalization !== true) {
            return;
        }

        if (!empty($this->config->country)) {
            $request->addHeaderLine("X-Imdb-User-Country", $this->config->country);
        }

        if (!empty($this->config->language)) {
            $request->addHeaderLine("X-Imdb-User-Language", $this->config->language);
        }
    }

    /**
     * Build the GraphQL request payload
     *
     * @param string $query GraphQL query
     * @param string|null $queryName Operation name
     * @param array $variables Query variables
     * @return string JSON encoded payload
     */
    private function buildPayload(string $query, ?string $queryName, array $variables): string
    {
        return json_encode([
            'operationName' => $queryName,
            'query' => $query,
            'variables' => (object)$variables
        ]);
    }

    /**
     * Handle the API response
     *
     * @param Request $request The request object
     * @param string|null $queryName The query name
     * @param array $variables The query variables
     * @return stdClass Response data
     * @throws Exception On unsuccessful response
     */
    private function handleResponse(Request $request, ?string $queryName, array $variables): stdClass
    {
        if ($request->getStatus() !== 200) {
            throw $this->createRequestException($queryName, $variables, 'Non-200 status code');
        }

        $responseBody = $request->getResponseBody();
        $decodedResponse = json_decode($responseBody);

        if (!isset($decodedResponse->data)) {
            throw $this->createRequestException($queryName, $variables, 'Invalid response format');
        }

        return $decodedResponse->data;
    }

    /**
     * Create a standardized exception for request failures
     *
     * @param string|null $queryName The query name
     * @param array $variables The query variables
     * @param string $message Base error message
     * @return Exception
     */
    private function createRequestException(?string $queryName, array $variables, string $message): Exception
    {
        $errorId = $variables['id'] ?? 'n/a';
        $queryName = $queryName ?? 'unnamed query';

        return new Exception(sprintf(
            '%s for query [%s], IMDb id [%s]',
            $message,
            $queryName,
            $errorId
        ));
    }
}
