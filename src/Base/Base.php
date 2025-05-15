<?php

namespace Hooshid\ImdbScraper\Base;

use DateTime;
use InvalidArgumentException;

/**
 * Base class for IMDb Scraper functionality
 *
 * Provides common utility methods and configuration for all IMDb scraper classes.
 */
class Base extends Config
{
    /** @var Config Configuration object */
    protected Config $config;

    /** @var GraphQL GraphQL client instance */
    protected GraphQL $graphql;

    /**
     * Constructor
     *
     * @param Config|null $config Optional configuration override
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?: $this;
        $this->graphql = new GraphQL($this->config);
    }

    /**
     * Clean and sanitize a string
     *
     * @param string|null $str The string to clean
     * @param array|string|null $remove Strings to remove
     * @return string|null Cleaned string or null if empty
     */
    protected function cleanString(?string $str, array|string $remove = null): ?string
    {
        if (empty($str)) {
            return null;
        }

        // Remove specified strings
        if (!empty($remove)) {
            $str = str_replace((array)$remove, '', $str);
        }

        // Replace common HTML entities
        $replacements = [
            '&amp;' => '&',
            '&nbsp;' => ' ',
            '&quot;' => '"',
            '&apos;' => "'"
        ];

        $str = strtr($str, $replacements);
        $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5);

        return trim(strip_tags($str)) ?: null;
    }

    /**
     * Generate image URLs in various sizes from original URL
     *
     * @param string|null $url Original image URL
     * @return array|null Array of image URLs or null if no URL provided
     */
    protected function imageUrl(?string $url = null): ?array
    {
        if (!$url) {
            return null;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL provided');
        }

        return [
            "original" => $url,
            "small" => @str_replace(".jpg", "UY74_CR41,0,50,74_.jpg", $url),
            "120x120" => @str_replace(".jpg", "UX120_CR0,0,120,120_.jpg", $url),
            "140" => @str_replace(".jpg", "UX140_.jpg", $url),
        ];
    }

    /**
     * Generate image URLs in various sizes from original URL
     *
     * @param object|null $obj
     * @return array|null Array of image URLs or null if no URL provided
     */
    protected function parseImage(?object $obj = null): ?array
    {
        if (!$obj) {
            return null;
        }

        $url = $obj->url ?? null;
        $width = $obj->width ?? null;
        $height = $obj->height ?? null;

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL provided');
        }

        return [
            "url" => $url,
            "width" => $width,
            "height" => $height
        ];
    }

    /**
     * Validate a date string in Y-m-d format
     *
     * @param string $date Date string to validate
     * @return bool True if valid, false otherwise
     */
    protected function validateDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * build date string in Y-m-d format
     *
     * @param $day
     * @param $month
     * @param $year
     * @return null|string
     */
    protected function buildDate($day, $month, $year): ?string
    {
        if (!empty($day) && !empty($month) && !empty($year)) {
            return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        } else {
            return null;
        }
    }

    /**
     * Convert seconds to minutes
     *
     * @param int|null $seconds
     * @return int|null
     */
    protected function secondsToMinutes(int $seconds = null): ?int
    {
        if (empty($seconds)) {
            return null;
        }

        return intval($seconds / 60);
    }

    /**
     * Convert seconds to time format
     * @param int|null $seconds
     * @return string|null
     */
    protected function secondsToTimeFormat(?int $seconds): ?string
    {
        if (empty($seconds)) {
            return null;
        }

        // Calculate hours, minutes, and seconds
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        // Determine the format based on whether there are hours
        if ($hours > 0) {
            return sprintf("%d:%d:%02d", $hours, $minutes, $seconds);
        } else {
            return sprintf("%d:%02d", $minutes, $seconds);
        }
    }

    /**
     * Make full IMDb url
     *
     * @param ...$params
     * @return string
     */
    protected function makeUrl(...$params): string
    {
        $baseUrl = "https://www.imdb.com/";
        $path = implode('/', $params);
        return rtrim($baseUrl . $path, '/') . '/';
    }
}
