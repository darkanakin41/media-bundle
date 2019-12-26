<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Twig;

use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Darkanakin41\MediaBundle\Model\File;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileInfoExtension extends AbstractExtension
{
    const EXTENSION_MAPPING = array(
        'css' => 'text/css',
        'twig' => 'text/html',
    );
    /**
     * @var Packages
     */
    private $packages;

    /** @var array */
    private $config;

    public function __construct(ParameterBagInterface $parameterBag, Packages $packages)
    {
        $this->packages = $packages;
        $this->config = $parameterBag->get(Darkanakin41MediaExtension::CONFIG_KEY);
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('darkanakin41_file_info_image_dimensions', array($this, 'getImageDimensions')),
        );
    }

    /**
     * Refresh filesystem information of the file.
     */
    public function refresh(File $file)
    {
        $file->setFilesize($this->getFileSize($file));
        $file->setFiletype($this->getFileType($file));
        $file->setDate($this->getFileDate($file));
    }

    /**
     * Get the size of the file on the filesystem.
     *
     * @return false|int
     */
    public function getFileSize(File $file)
    {
        return filesize($this->getFullPath($file));
    }

    /**
     * Get the type of the file.
     *
     * @return mixed|string
     */
    public function getFileType(File $file)
    {
        $fileExploded = explode('.', $file->getFilepath());
        $extension = array_pop($fileExploded);
        if (in_array($extension, array_keys(self::EXTENSION_MAPPING))) {
            return self::EXTENSION_MAPPING[$extension];
        }

        return mime_content_type($this->getFullPath($file));
    }

    /**
     * Get the filesystem date of the file.
     *
     * @return \DateTime|false
     */
    public function getFileDate(File $file)
    {
        return \DateTime::createFromFormat('U', filemtime($this->getFullPath($file)));
    }

    /**
     * Get the public URL of the file.
     *
     * @return mixed
     */
    public function getUrl(File $file)
    {
        return $this->packages->getUrl($file->getFilepath());
    }

    /**
     * Convert the File into an array of main data.
     *
     * @return array
     */
    public function toArray(File $file)
    {
        return array(
            'id' => $file->getId(),
            'filename' => $file->getFilename(),
            'filepath' => $this->getUrl($file),
            'filesize' => $file->getFilesize(),
            'filetype' => $file->getFiletype(),
            'dimensions' => $this->getImageDimensions($file),
        );
    }

    /**
     * Retrieve the dimension of the image.
     *
     * @return array [width, height]
     */
    public function getImageDimensions(File $file)
    {
        $dimensions = array();
        if (false === stripos($file->getFileType(), 'image')) {
            return $dimensions;
        }
        $data = getimagesize($this->getFullPath($file));
        $dimensions = array('width' => $data[0], 'height' => $data[1]);

        return $dimensions;
    }

    /**
     * Get the full path of the file on the server.
     *
     * @return string the full path
     */
    public function getFullPath(File $file)
    {
        return $this->config['base_folder'].$file->getFilepath();
    }
}
