<?php

namespace Hooshid\ImdbScraper\Base;

/**
 * Image processing for IMDb-style thumbnails
 *
 * Calculates URL parameters for generating cropped/resized thumbnails
 * matching IMDb's image processing patterns.
 */
class Image
{
    private const DEFAULT_QUALITY = 75;
    private const MIN_CROP_VALUE = 0;

    /**
     * Generate thumbnail url
     *
     * @param string $url original image url
     * @param int $fullImageWidth Original image width in pixels
     * @param int $fullImageHeight Original image height in pixels
     * @param int $newImageWidth Desired thumbnail width
     * @param int $newImageHeight Desired thumbnail height
     * @return string new thumbnail url
     */
    public function makeThumbnail(
        string $url,
        int    $fullImageWidth,
        int    $fullImageHeight,
        int    $newImageWidth,
        int    $newImageHeight): string
    {
        $url = str_replace('.jpg', '', $url);
        $parameter = $this->resultParameter($fullImageWidth, $fullImageHeight, $newImageWidth, $newImageHeight);
        return $url . $parameter;
    }

    /**
     * Generate IMDb-style thumbnail URL parameters
     *
     * @param int $fullImageWidth Original image width in pixels
     * @param int $fullImageHeight Original image height in pixels
     * @param int $newImageWidth Desired thumbnail width
     * @param int $newImageHeight Desired thumbnail height
     * @return string URL parameters (e.g. 'QL75_SX190_CR0,15,190,281_.jpg')
     *
     * Structure:
     * QL{quality} - Quality Level (75 default)
     * SX/SY{size} - Scale axis and target size
     * CR{crop} - Crop parameters (left,top,width,height)
     */
    public function resultParameter(
        int $fullImageWidth,
        int $fullImageHeight,
        int $newImageWidth,
        int $newImageHeight
    ): string
    {
        $ratioOriginal = $fullImageWidth / $fullImageHeight;
        $ratioNew = $newImageWidth / $newImageHeight;

        if ($ratioNew < $ratioOriginal) {
            return $this->generateHorizontalCropParameters(
                $fullImageWidth,
                $fullImageHeight,
                $newImageWidth,
                $newImageHeight
            );
        }

        return $this->generateVerticalCropParameters(
            $fullImageWidth,
            $fullImageHeight,
            $newImageWidth,
            $newImageHeight
        );
    }

    /**
     * Generate parameters for horizontally-cropped thumbnail
     */
    private function generateHorizontalCropParameters(
        int $fullWidth,
        int $fullHeight,
        int $newWidth,
        int $newHeight
    ): string
    {
        $cropValue = $this->calculateHorizontalCrop(
            $fullWidth,
            $fullHeight,
            $newWidth,
            $newHeight
        );

        return sprintf(
            'QL%d_UY%d_CR%d,0,%d,%d_.jpg',
            self::DEFAULT_QUALITY,
            $newHeight,
            $cropValue,
            $newWidth,
            $newHeight
        );
    }

    /**
     * Generate parameters for vertically-cropped thumbnail
     */
    private function generateVerticalCropParameters(
        int $fullWidth,
        int $fullHeight,
        int $newWidth,
        int $newHeight
    ): string
    {
        $cropValue = $this->calculateVerticalCrop(
            $fullWidth,
            $fullHeight,
            $newWidth,
            $newHeight
        );

        return sprintf(
            'QL%d_UX%d_CR0,%d,%d,%d_.jpg',
            self::DEFAULT_QUALITY,
            $newWidth,
            $cropValue,
            $newWidth,
            $newHeight
        );
    }

    /**
     * Calculate horizontal (left/right) crop value
     */
    public function calculateHorizontalCrop(
        int $fullWidth,
        int $fullHeight,
        int $newWidth,
        int $newHeight
    ): int
    {
        $scaleFactor = $fullHeight / $newHeight;
        $scaledWidth = $fullWidth / $scaleFactor;
        $totalCrop = $scaledWidth - $newWidth;

        return max($this->roundToEven($totalCrop) / 2, self::MIN_CROP_VALUE);
    }

    /**
     * Calculate vertical (top/bottom) crop value
     */
    public function calculateVerticalCrop(
        int $fullWidth,
        int $fullHeight,
        int $newWidth,
        int $newHeight
    ): int
    {
        $scaleFactor = $fullWidth / $newWidth;
        $scaledHeight = $fullHeight / $scaleFactor;
        $totalCrop = $scaledHeight - $newHeight;

        return max($this->roundToEven($totalCrop) / 2, self::MIN_CROP_VALUE);
    }

    /**
     * Round to nearest even integer with special midpoint handling
     */
    private function roundToEven(float $value): int
    {
        $fraction = $value - floor($value);

        if ($fraction < 0.5) {
            return (int)(2 * round($value / 2));
        }

        $rounded = (int)ceil($value);
        return $rounded % 2 === 0 ? $rounded : $rounded + 1;
    }

    /**
     * Calculate proportional width for a given height
     */
    public function calculateProportionalWidth(
        int $fullWidth,
        int $fullHeight,
        int $newHeight
    ): int
    {
        $scaleFactor = $fullHeight / $newHeight;
        return (int)ceil($fullWidth / $scaleFactor);
    }
}
