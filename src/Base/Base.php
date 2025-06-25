<?php

namespace Hooshid\ImdbScraper\Base;

use DateTime;
use InvalidArgumentException;

/**
 * Base class for IMDb Scraper functionality
 *
 * Provides common utility methods for:
 * - String cleaning and sanitization
 * - Image URL manipulation
 * - Date/time formatting and validation
 * - URL construction
 */
class Base extends Config
{
    protected const IMDB_BASE_URL = 'https://www.imdb.com/';

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
     * Safely checks if a nested object property exists and is an array
     *
     * @param mixed $property The nested property path to check (e.g., $data->mainSearch->edges)
     * @return bool True if property exists and is an array, false otherwise
     */
    function hasArrayItems(mixed $property): bool
    {
        return isset($property) && is_array($property) && count($property) > 0;
    }

    /**
     * Parse image object into structured data
     *
     * @param object|null $imageObject Object containing image data
     * @return array{url: string|mixed|null, width: int|null, height: int|null}|null
     * @throws InvalidArgumentException If URL is invalid
     */
    protected function parseImage(?object $imageObject = null): ?array
    {
        if (empty($imageObject)) {
            return null;
        }

        $url = $imageObject->url ?? null;
        $width = $imageObject->width ?? null;
        $height = $imageObject->height ?? null;

        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL provided');
        }

        return [
            'url' => $url,
            'width' => is_numeric($width) ? (int)$width : null,
            'height' => is_numeric($height) ? (int)$height : null
        ];
    }

    /**
     * Validate a date string in Y-m-d format
     *
     * @param string $dateString Date string to validate
     * @return bool True if valid date string, false otherwise
     */
    protected function validateDate(string $dateString): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        return $date && $date->format('Y-m-d') === $dateString;
    }

    /**
     * Build date string in Y-m-d format from components
     *
     * @param int|string|null $day Day component
     * @param int|string|null $month Month component
     * @param int|string|null $year Year component
     * @return string|null Formatted date string or null if any component is missing
     */
    protected function buildDate(int|string|null $day, int|string|null $month, int|string|null $year): ?string
    {
        if (empty($day) || empty($month) || empty($year)) {
            return null;
        }

        return sprintf(
            '%d-%02d-%02d',
            (string)$year,
            str_pad((string)$month, 2, '0', STR_PAD_LEFT),
            str_pad((string)$day, 2, '0', STR_PAD_LEFT)
        );
    }

    /**
     * Convert seconds to minutes
     *
     * @param int|null $seconds Time in seconds
     * @return int|null Time in minutes or null if input is empty
     */
    protected function secondsToMinutes(?int $seconds): ?int
    {
        return empty($seconds) ? null : (int)floor($seconds / 60);
    }

    /**
     * Convert seconds to HH:MM:SS or MM:SS time format
     *
     * @param int|null $seconds Time in seconds
     * @return string|null Formatted time string or null if input is empty
     */
    protected function secondsToTimeFormat(?int $seconds): ?string
    {
        if (empty($seconds)) {
            return null;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Make full IMDb url
     *
     * @param string ...$pathComponents URL paths
     * @return string Complete IMDb URL
     */
    protected function makeUrl(string ...$pathComponents): string
    {
        $path = implode('/', array_filter($pathComponents));
        return rtrim(self::IMDB_BASE_URL . $path, '/') . '/';
    }
}
