<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Tools;

use Exception;

class ResizeImage
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $ext;
    /**
     * @var int
     */
    private $originWidth;
    /**
     * @var int
     */
    private $originHeight;

    /**
     * @var int
     */
    private $resizeWidth;

    /**
     * @var int
     */
    private $resizeHeight;

    /**
     * @var resource
     */
    private $image;
    /**
     * @var resource
     */
    private $newImage;

    /**
     * ResizeImage constructor.
     *
     * @param $file
     *
     * @throws \Exception
     */
    public function __construct($file)
    {
        if (file_exists($file)) {
            $this->setFile($file);
        } else {
            throw new \Exception('Image '.$file.' can not be found, try another image.');
        }
    }

    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @throws Exception
     */
    public function setFile(string $file): ResizeImage
    {
        $size = getimagesize($file);
        $this->file = $file;
        $this->ext = $size['mime'];
        switch ($this->ext) {
            // Image is a JPG
            case 'image/jpg':
            case 'image/jpeg':
                // create a jpeg extension
                $this->image = imagecreatefromjpeg($file);
                break;
            // Image is a GIF
            case 'image/gif':
                $this->image = @imagecreatefromgif($file);
                break;
            // Image is a PNG
            case 'image/png':
                $this->image = @imagecreatefrompng($file);
                break;
            // Mime type not found
            default:
                throw new Exception('File is not an image, please use another file type.', 1);
        }
        $this->originWidth = imagesx($this->image);
        $this->originHeight = imagesy($this->image);

        return $this;
    }

    /**
     * Resize the image to these set dimensions.
     *
     * @param int    $width        Max width of the image
     * @param int    $height       Max height of the image
     * @param string $resizeOption Scale option for the image
     */
    public function resizeTo($width, $height, $resizeOption = 'default'): void
    {
        switch (strtolower($resizeOption)) {
            case 'exact':
                $this->resizeWidth = $width;
                $this->resizeHeight = $height;
                break;
            case 'maxwidth':
                $this->resizeWidth = $width;
                $this->resizeHeight = $this->resizeHeightByWidth($width);
                break;
            case 'maxheight':
                $this->resizeWidth = $this->resizeWidthByHeight($height);
                $this->resizeHeight = $height;
                break;
            default:
                if ($this->originWidth > $width || $this->originHeight > $height) {
                    if ($this->originWidth > $this->originHeight) {
                        $this->resizeHeight = $this->resizeHeightByWidth($width);
                        $this->resizeWidth = $width;
                    } elseif ($this->originWidth < $this->originHeight) {
                        $this->resizeWidth = $this->resizeWidthByHeight($height);
                        $this->resizeHeight = $height;
                    }
                } else {
                    $this->resizeWidth = $width;
                    $this->resizeHeight = $height;
                }
                break;
        }
        $this->newImage = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        imagealphablending($this->newImage, false);
        imagesavealpha($this->newImage, true);
        imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->originWidth, $this->originHeight);
    }

    /**
     * Save the image as the image type the original image was.
     *
     * @param string $savePath The path to store the new image
     */
    public function saveImage($savePath, $imageQuality = 100)
    {
        switch ($this->ext) {
            case 'image/jpg':
            case 'image/jpeg':
                // Check PHP supports this file type
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->newImage, $savePath, $imageQuality);
                }
                break;
            case 'image/gif':
                // Check PHP supports this file type
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->newImage, $savePath);
                }
                break;
            case 'image/png':
                $invertScaleQuality = 9 - round(($imageQuality / 100) * 9);
                // Check PHP supports this file type
//                imagealphablending( $this->newImage, false );
//                imagesavealpha( $this->newImage, true );
                if (imagetypes() & IMG_PNG) {
                    imagepng($this->newImage, $savePath, $invertScaleQuality);
                }
                break;
        }
        imagedestroy($this->newImage);
    }

    /**
     * Get the resized height from the width keeping the aspect ratio.
     *
     * @param int $width Max image width
     *
     * @return int height keeping aspect ratio
     */
    private function resizeHeightByWidth($width): int
    {
        return floor(($this->originHeight / $this->originWidth) * $width);
    }

    /**
     * Get the resized width from the height keeping the aspect ratio.
     *
     * @param int $height Max image height
     *
     * @return int Width keeping aspect ratio
     */
    private function resizeWidthByHeight($height): int
    {
        return floor(($this->originWidth / $this->originHeight) * $height);
    }
}
