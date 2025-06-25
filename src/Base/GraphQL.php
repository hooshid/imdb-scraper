<?php

namespace Hooshid\ImdbScraper\Base;

use Exception;
use JsonException;
use stdClass;

/**
 * Client for accessing IMDb data through GraphQL API
 *
 * Handles all GraphQL requests to the IMDb API with support for:
 * - Custom queries and mutations
 * - Variables
 * - Localization headers (country/language)
 * - Error handling
 */
class GraphQL
{
    private const API_ENDPOINT = 'https://api.graphql.imdb.com/';

    /** @var Config Configuration object */
    private Config $config;

    /**
     * Initialize GraphQL client with configuration
     *
     * @param Config $config Configuration object containing localization settings
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Execute a GraphQL query
     *
     * @param string $query The GraphQL query/mutation string
     * @param string $operationName Name of the query (operation name)
     * @param array<string, mixed> $variables Variables to pass with the query
     * @return stdClass Response data
     * @throws Exception On request failure, invalid response, or JSON errors
     */
    public function query(string $query, string $operationName, array $variables = []): stdClass
    {
        $request = new Request(self::API_ENDPOINT);
        $this->configureRequestHeaders($request);

        $payload = $this->buildRequestPayload($query, $operationName, $variables);
        if (!$request->post($payload)) {
            throw $this->createRequestException($operationName, $variables, 'Request failed');
        }

        return $this->processResponse($request, $operationName, $variables);
    }

    /**
     * Configure request headers including localization settings
     *
     * @param Request $request Request object to configure
     */
    private function configureRequestHeaders(Request $request): void
    {
        $request->addHeaderLine("Content-Type", "application/json");

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
     * @param string $operationName Name of the operation
     * @param array<string, mixed> $variables Query variables
     * @return string JSON encoded payload
     * @throws JsonException On JSON encoding failure
     */
    private function buildRequestPayload(string $query, string $operationName, array $variables): string
    {
        // Normalize GraphQL query by removing unnecessary whitespace
        $normalizedQuery = implode("\n", array_map('trim', explode("\n", $query)));

        return json_encode([
            'operationName' => $operationName,
            'query' => $normalizedQuery,
            'variables' => (object)$variables
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Process and validate the API response
     *
     * @param Request $request The completed request object
     * @param string $operationName The operation name
     * @param array<string, mixed> $variables The query variables
     * @return stdClass Response data
     * @throws Exception On unsuccessful or invalid response
     */
    private function processResponse(Request $request, string $operationName, array $variables): stdClass
    {
        if ($request->getStatusCode() !== 200) {
            throw $this->createRequestException(
                $operationName,
                $variables,
                sprintf('Received status code %d', $request->getStatusCode())
            );
        }

        try {
            $responseData = json_decode($request->getResponseBody(), false, 512, JSON_THROW_ON_ERROR);

            // Ensure the decoded data is an object
            if (!is_object($responseData)) {
                throw new Exception('Invalid response format - expected JSON object');
            }

            if (!isset($responseData->data)) {
                throw $this->createRequestException(
                    $operationName,
                    $variables,
                    'Invalid response format'
                );
            }

            return (object)$responseData->data;
        } catch (JsonException $e) {
            throw new Exception('Failed to decode JSON response: ' . $e->getMessage());
        }
    }

    /**
     * Create a standardized exception for request failures
     *
     * @param string $operationName The operation name
     * @param array<string, mixed> $variables The query variables
     * @param string $message Base error message
     * @return Exception Detailed exception with context
     */
    private function createRequestException(string $operationName, array $variables, string $message): Exception
    {
        return new Exception(sprintf(
            '%s (operation: %s, variables: %s)',
            $message,
            $operationName,
            json_encode($variables)
        ));
    }
}
