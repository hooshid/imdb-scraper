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
    /** @var CurlHandle|false cURL handle */
    private false|CurlHandle $ch;

    /** @var string|bool Response body or false on failure */
    private bool|string $page;

    /** @var array Request headers to be sent */
    private array $requestHeaders = [];

    /** @var array Response headers received */
    private array $responseHeaders = [];

    /**
     * Initialize a new request
     *
     * @param string $url URL to request
     * @throws RuntimeException If cURL initialization fails
     */
    public function __construct(string $url)
    {
        $this->ch = curl_init($url);

        if ($this->ch === false) {
            throw new RuntimeException('Failed to initialize cURL handle');
        }

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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            CURLOPT_TIMEOUT => 45,
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
     * @param array|string $content POST data
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

        if (!empty($this->requestHeaders)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        }

        $this->page = curl_exec($this->ch);

        if ($this->page !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get the response body
     *
     * @return string Response content
     * @throws RuntimeException If no response is available
     */
    public function getResponseBody(): string
    {
        if ($this->page === false) {
            throw new RuntimeException('No response available');
        }

        return $this->page;
    }

    /**
     * Get the HTTP status code of the last response
     *
     * @return int|null HTTP status code or null if unavailable
     */
    public function getStatus(): ?int
    {
        $headers = $this->getLastResponseHeaders();

        if (empty($headers[0])) {
            return null;
        }

        if (!preg_match("#^HTTP/[\d\.]+ (\d+)#i", $headers[0], $matches)) {
            return null;
        }

        return (int)$matches[1];
    }

    /**
     * Get all response headers from the last request
     *
     * @return array Response headers
     */
    public function getLastResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * cURL header callback function
     *
     * @param CurlHandle $ch cURL handle
     * @param string $str Header line
     * @return int Length of the header line
     */
    private function headerCallback(CurlHandle $ch, string $str): int
    {
        $length = strlen($str);

        if ($length > 0) {
            $this->responseHeaders[] = $str;
        }

        return $length;
    }

    /**
     * Close the cURL handle when object is destroyed
     */
    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }
}
