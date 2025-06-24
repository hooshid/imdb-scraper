<?php

namespace Hooshid\ImdbScraper\Base;

use InvalidArgumentException;

/**
 * Image processing for IMDb-style thumbnails
 *
 * Calculates URL parameters for generating cropped/resized thumbnails
 * matching IMDb's image processing patterns.
 */
class Image
{
    /**
     * Generate thumbnail URL from original image
     *
     * @param string $originalUrl Original image URL
     * @param int $originalWidth Original image width in pixels
     * @param int $originalHeight Original image height in pixels
     * @param int $targetWidth Desired thumbnail width in pixels
     * @param int $targetHeight Desired thumbnail height in pixels
     * @param int $quality Thumbnail quality (1-100)
     * @return string Generated thumbnail URL
     * @throws InvalidArgumentException If invalid dimensions or quality are provided
     */
    public function makeThumbnail(
        string $originalUrl,
        int    $originalWidth,
        int    $originalHeight,
        int    $targetWidth,
        int    $targetHeight,
        int    $quality = 100
    ): string
    {
        if ($originalWidth <= 0 || $originalHeight <= 0) {
            throw new InvalidArgumentException('Original dimensions must be positive');
        }

        if ($targetWidth <= 0 || $targetHeight <= 0) {
            throw new InvalidArgumentException('Target dimensions must be positive');
        }

        if ($quality < 1 || $quality > 100) {
            throw new InvalidArgumentException('Quality must be between 1 and 100');
        }

        $parameters = $this->generateThumbnailParameters(
            $originalWidth,
            $originalHeight,
            $targetWidth,
            $targetHeight,
            $quality
        );
        return str_replace('.jpg', '', $originalUrl) . $parameters;
    }

    /**
     * Generate thumbnail URL parameters based on dimensions
     *
     * @param int $originalWidth Original image width
     * @param int $originalHeight Original image height
     * @param int $targetWidth Target thumbnail width
     * @param int $targetHeight Target thumbnail height
     * @param int $quality Image quality (1-100)
     * @return string URL parameters (e.g. 'QL100_SX190_CR0,15,190,281_.jpg')
     */
    public function generateThumbnailParameters(
        int $originalWidth,
        int $originalHeight,
        int $targetWidth,
        int $targetHeight,
        int $quality = 100
    ): string
    {
        $originalRatio = $originalWidth / $originalHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($targetRatio < $originalRatio) {
            return $this->generateHorizontalCropParameters(
                $originalWidth,
                $originalHeight,
                $targetWidth,
                $targetHeight,
                $quality
            );
        }

        return $this->generateVerticalCropParameters(
            $originalWidth,
            $originalHeight,
            $targetWidth,
            $targetHeight,
            $quality
        );
    }

    /**
     * Generate parameters for horizontally-cropped thumbnail (left/right crop)
     */
    private function generateHorizontalCropParameters(
        int $originalWidth,
        int $originalHeight,
        int $targetWidth,
        int $targetHeight,
        int $quality
    ): string
    {
        // Calculate horizontal (left/right) crop amount
        $scaleFactor = $originalHeight / $targetHeight;
        $scaledWidth = $originalWidth / $scaleFactor;
        $totalCrop = $scaledWidth - $targetWidth;
        $cropValue = max($this->roundInteger($totalCrop) / 2, 0);

        return sprintf(
            'QL%d_UY%d_CR%d,0,%d,%d_.jpg',
            $quality,
            $targetHeight,
            $cropValue,
            $targetWidth,
            $targetHeight
        );
    }

    /**
     * Generate parameters for vertically-cropped thumbnail (top/bottom crop)
     */
    private function generateVerticalCropParameters(
        int $originalWidth,
        int $originalHeight,
        int $targetWidth,
        int $targetHeight,
        int $quality
    ): string
    {
        // Calculate vertical (top/bottom) crop amount
        $scaleFactor = $originalWidth / $targetWidth;
        $scaledHeight = $originalHeight / $scaleFactor;
        $totalCrop = $scaledHeight - $targetHeight;
        $cropValue = max($this->roundInteger($totalCrop) / 2, 0);

        return sprintf(
            'QL%d_UX%d_CR0,%d,%d,%d_.jpg',
            $quality,
            $targetWidth,
            $cropValue,
            $targetWidth,
            $targetHeight
        );
    }

    /**
     * Rounds the crop value to the nearest even integer.
     * If the fractional part is less than 0.5, it rounds to the previous even integer.
     * Otherwise, it rounds to the next even integer.
     *
     * @param float $totalPixelCropSize Total number of pixels to crop.
     * @return int Rounded even integer.
     */
    private function roundInteger(float $totalPixelCropSize): int
    {
        $fraction = $totalPixelCropSize - floor($totalPixelCropSize);

        if ($fraction < 0.5) {
            // Round down to previous even integer
            return (int)(2 * floor($totalPixelCropSize / 2));
        }

        // Round up to next even integer
        $roundedUp = (int)ceil($totalPixelCropSize);
        return $roundedUp % 2 === 0 ? $roundedUp : $roundedUp + 1;
    }
}
