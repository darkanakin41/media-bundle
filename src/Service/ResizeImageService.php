<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Service;

use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ResizeImageService
{
    /** @var array */
    private $config;

    /**
     * ResizeImage constructor.
     *
     * @throws Exception
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->config = $parameterBag->get(Darkanakin41MediaExtension::CONFIG_KEY);
    }

    /**
     * Process the filename based on the configuration defined.
     *
     * @param string $category
     *
     * @throws Exception
     */
    public function process(string $filename, $category)
    {
        if (!file_exists($filename)) {
            // TODO Replace by UnknownFileException
            throw new Exception(sprintf('The requested file (%s) is not accessible', $filename));
        }

        if (!isset($this->config['image_formats'][$category])) {
            // TODO Replace by UnknownResizeCategoryException
            throw new Exception(sprintf('The requested category (%s) is not defined in configuration', $category));
        }

        $file = $this->getResource($filename);
        $originalDimensions = $this->getOriginalDimensions($file);

        foreach ($this->config['image_formats'][$category] as $format => $settings) {
            $finalDimensions = $this->getFinalDimensions($file, $settings['width'], $settings['height'], $settings['resize']);
            $newFile = $this->resizeTo($file, $originalDimensions['width'], $originalDimensions['height'], $finalDimensions['width'], $finalDimensions['height']);

            $resizeFileName = $this->getResizedPath($filename, $format);

            $this->saveImage($newFile, $resizeFileName, $settings['quality']);
        }
    }

    /**
     * Generate the resized path.
     *
     * @param $filename
     * @param $format
     *
     * @return string
     */
    public function getResizedPath($filename, $format)
    {
        $extension = $this->getFileExtension($filename);

        return str_ireplace(sprintf('.%s', $extension), sprintf('-%s.%s', $format, $extension), $filename);
    }

    /**
     * Retrieve the list of other format for the given baseFile.
     *
     * @return array [[path, min-width]...]
     */
    public function getResizedFiles(string $baseFile, string $category)
    {
        $versions = array();

        if (!isset($this->config['image_formats'][$category])) {
            return $versions;
        }

        foreach ($this->config['image_formats'][$category] as $format => $settings) {
            $resizedPath = $this->getResizedPath($baseFile, $format);
            if (file_exists($resizedPath)) {
                $version = array(
                    'path' => $resizedPath,
                );

                if (isset($settings['min_width'])) {
                    $version['minWidth'] = $settings['min_width'];
                }

                $versions[$format] = $version;
            }
        }

        return $versions;
    }

    /**
     * Create the resource based on the file type.
     *
     * @return resource
     *
     * @throws Exception in case of not image file mime type
     */
    public function getResource(string $filename)
    {
        $size = @getimagesize($filename);
        $resource = null;
        switch ($size['mime']) {
            case 'image/jpg':
            case 'image/jpeg':
                $resource = imagecreatefromjpeg($filename);
                break;
            case 'image/gif':
                $resource = @imagecreatefromgif($filename);
                break;
            case 'image/png':
                $resource = @imagecreatefrompng($filename);
                break;
            default:
                throw new Exception('Unknown file type');
        }

        return $resource;
    }

    /**
     * Retrieve the original dimensions.
     *
     * @param resource $file
     *
     * @return array [width, height]
     */
    public function getOriginalDimensions($file)
    {
        return array(
            'width' => imagesx($file),
            'height' => imagesy($file),
        );
    }

    /**
     * Get the calculated final dimensions.
     *
     * @param resource $file
     * @param float    $targetWidth
     * @param float    $targetHeight
     * @param string   $resizeOption
     *
     * @return array
     */
    public function getFinalDimensions($file, $targetWidth, $targetHeight, $resizeOption = 'default')
    {
        $originalDimensions = $this->getOriginalDimensions($file);

        $finalWidth = $targetWidth;
        $finalHeight = $targetHeight;

        switch (strtolower($resizeOption)) {
            case 'exact':
                $finalWidth = $targetWidth;
                $finalHeight = $targetHeight;
                break;
            case 'maxwidth':
                $finalWidth = $targetWidth;
                $finalHeight = $this->resizeHeightByWidth($targetWidth, $originalDimensions['width'], $originalDimensions['height']);
                break;
            case 'maxheight':
                $finalWidth = $this->resizeWidthByHeight($targetHeight, $originalDimensions['width'], $originalDimensions['height']);
                $finalHeight = $targetHeight;
                break;
            default:
                if ($originalDimensions['width'] > $targetWidth || $originalDimensions['height'] > $targetHeight) {
                    if ($originalDimensions['width'] > $originalDimensions['height']) {
                        $finalHeight = $this->resizeHeightByWidth($targetWidth, $originalDimensions['width'], $originalDimensions['height']);
                        $finalWidth = $targetWidth;
                    } elseif ($originalDimensions['width'] < $originalDimensions['height']) {
                        $finalWidth = $this->resizeWidthByHeight($targetHeight, $originalDimensions['width'], $originalDimensions['height']);
                        $finalHeight = $targetHeight;
                    }
                }
                break;
        }

        return array(
            'width' => $finalWidth,
            'height' => $finalHeight,
        );
    }

    /**
     * Get the resized height from the width keeping the aspect ratio.
     *
     * @param int   $width          Max image width
     * @param float $originalWidth  the width of the original image
     * @param float $originalHeight the height of the original image
     *
     * @return int height keeping aspect ratio
     */
    public function resizeHeightByWidth($width, $originalWidth, $originalHeight): int
    {
        return floor(($originalHeight / $originalWidth) * $width);
    }

    /**
     * Get the resized width from the height keeping the aspect ratio.
     *
     * @param int   $height         Max image height
     * @param float $originalWidth  the width of the original image
     * @param float $originalHeight the height of the original image
     *
     * @return int Width keeping aspect ratio
     */
    public function resizeWidthByHeight($height, $originalWidth, $originalHeight): int
    {
        return floor(($originalWidth / $originalHeight) * $height);
    }

    /**
     * Resize the image to these set dimensions.
     *
     * @param resource $file           the file to resize
     * @param float    $originalWidth  the width of the original image
     * @param float    $originalHeight the height of the original image
     * @param float    $targetWidth    the width of the new image
     * @param float    $targetHeight   the height of the new image
     *
     * @return resource
     */
    public function resizeTo($file, $originalWidth, $originalHeight, $targetWidth, $targetHeight)
    {
        $newImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $file, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);

        return $newImage;
    }

    /**
     * Save the image as the image type the original image was.
     *
     * @param resource $file         the image to save
     * @param string   $savePath     The path to store the new image
     * @param int      $imageQuality the quality to save it to
     */
    public function saveImage($file, $savePath, $imageQuality = 100)
    {
        switch ($this->getFileExtension($savePath)) {
            case 'jpg':
            case 'jpeg':
                // Check PHP supports this file type
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($file, $savePath, $imageQuality);
                }
                break;
            case 'gif':
                // Check PHP supports this file type
                if (imagetypes() & IMG_GIF) {
                    imagegif($file, $savePath);
                }
                break;
            case 'png':
                $invertScaleQuality = 9 - round(($imageQuality / 100) * 9);
                // Check PHP supports this file type
                if (imagetypes() & IMG_PNG) {
                    imagepng($file, $savePath, $invertScaleQuality);
                }
                break;
        }
        imagedestroy($file);
    }

    /**
     * Retrieve the file extension.
     *
     * @return string
     */
    public function getFileExtension(string $filename)
    {
        $filenameArray = explode('.', $filename);

        return strtolower(end($filenameArray));
    }
}
