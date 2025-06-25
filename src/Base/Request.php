<?php

namespace Hooshid\ImdbScraper\Base;

use CurlHandle;
use RuntimeException;

/**
 * Handles HTTP requests for IMDb scraping
 *
 * This class provides a wrapper around cURL functionality for making HTTP requests,
 * with support for custom headers, POST requests, and response handling.
 */
class Request
{
    private const DEFAULT_TIMEOUT = 45;
    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0';

    /** @var CurlHandle cURL handle */
    private CurlHandle $ch;

    /** @var string|null Response body or null if request failed */
    private ?string $page = null;

    /** @var array<int, string> Request headers to be sent */
    private array $requestHeaders = [];

    /** @var array<int, string> Response headers received */
    private array $responseHeaders = [];

    /**
     * Initialize a new request
     *
     * @param string $url URL to request
     * @throws RuntimeException If cURL initialization fails
     */
    public function __construct(string $url)
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL handle');
        }

        $this->ch = $ch;
        $this->configureDefaultCurlOptions();
    }

    /**
     * Configure default cURL options
     */
    private function configureDefaultCurlOptions(): void
    {
        curl_setopt_array($this->ch, [
            CURLOPT_ENCODING => "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADERFUNCTION => [$this, "headerCallback"],
            CURLOPT_USERAGENT => self::DEFAULT_USER_AGENT,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_FAILONERROR => true,
        ]);
    }

    /**
     * Add a header to the request
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return void
     */
    public function addHeaderLine(string $name, string $value): void
    {
        $this->requestHeaders[] = "$name: $value";
    }

    /**
     * Send a POST request
     *
     * @param array<string, mixed>|string $content POST data
     * @return bool True on success, false on failure
     */
    public function post(array|string $content): bool
    {
        curl_setopt_array($this->ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        ]);

        return $this->sendRequest();
    }

    /**
     * Send the HTTP request
     *
     * @return bool True on success, false on failure
     */
    public function sendRequest(): bool
    {
        $this->responseHeaders = [];
        $this->page = null;

        if (!empty($this->requestHeaders)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        }

        $response = curl_exec($this->ch);
        curl_close($this->ch);
        if (is_string($response)) {
            $this->page = $response;
            return true;
        }

        return false;
    }

    /**
     * Get the response body
     *
     * @return string Response content
     * @throws RuntimeException If no response is available or request failed
     */
    public function getResponseBody(): string
    {
        if ($this->page === null) {
            $error = curl_error($this->ch) ?: 'No response available';
            throw new RuntimeException('Request failed: ' . $error);
        }

        return $this->page;
    }

    /**
     * Get the HTTP status code of the last response
     *
     * @return int HTTP status code
     * @throws RuntimeException If status code cannot be determined
     */
    public function getStatusCode(): int
    {
        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($statusCode === 0) {
            throw new RuntimeException('Unable to determine HTTP status code');
        }

        return $statusCode;
    }

    /**
     * Get all response headers from the last request
     *
     * @return array<int, string> Response headers
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * cURL header callback function
     *
     * @param CurlHandle $ch cURL handle
     * @param string $headerLine Header line
     * @return int Length of the header line
     */
    private function headerCallback(CurlHandle $ch, string $headerLine): int
    {
        $length = strlen($headerLine);

        if ($length > 0) {
            $this->responseHeaders[] = $headerLine;
        }

        return $length;
    }
}
