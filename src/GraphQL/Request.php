<?php

namespace Hooshid\ImdbScraper\GraphQL;

use CurlHandle;

class Request
{
    private false|CurlHandle $ch;
    private $page;
    private array $requestHeaders = [];
    private array $responseHeaders = [];

    /**
     * No need to call this.
     * @param string $url URL to open
     */
    public function __construct(string $url)
    {
        $this->ch = curl_init($url);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, "callback_CURLOPT_HEADERFUNCTION"));
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
    }

    public function addHeaderLine($name, $value): void
    {
        $this->requestHeaders[] = "$name: $value";
    }

    /**
     * Send a POST request
     *
     * @param array|string $content
     * @return bool
     */
    public function post(array|string $content): bool
    {
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
        return $this->sendRequest();
    }

    /**
     * Send a request to the movie site
     */
    public function sendRequest(): bool
    {
        $this->responseHeaders = [];
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        $this->page = curl_exec($this->ch);
        curl_close($this->ch);

        if ($this->page !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get the Response body
     * @return string page
     */
    public function getResponseBody(): string
    {
        return $this->page;
    }

    /**
     * HTTP status code of the last response
     * @return int|null null if last request failed
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

    public function getLastResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    private function callback_CURLOPT_HEADERFUNCTION($ch, $str): int
    {
        $len = strlen($str);
        if ($len) {
            $this->responseHeaders[] = $str;
        }
        return $len;
    }
}
