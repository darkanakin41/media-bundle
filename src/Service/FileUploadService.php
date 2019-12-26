<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Service;

use Darkanakin41\CoreBundle\Tools\Slugify;
use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Darkanakin41\MediaBundle\Model\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    const PATH_RELATIVE = 'relative';
    const PATH_ABSOLUTE = 'absolute';

    /**
     * @var array
     */
    private $config;
    /**
     * @var ResizeImageService
     */
    private $resizeImage;

    /**
     * FileUpload constructor.
     */
    public function __construct(ParameterBagInterface $parameterBag, ResizeImageService $resizeImage)
    {
        $this->config = $parameterBag->get(Darkanakin41MediaExtension::CONFIG_KEY);
        $this->resizeImage = $resizeImage;
    }

    /**
     * Upload the file in the target folder.
     *
     * @param string $name
     * @param string $category
     *
     * @return string
     *
     * @throws \Exception
     */
    public function upload(UploadedFile $file, $name = 'image', $category = '')
    {
        $now = new \DateTime('now');

        $extension = $file->getClientOriginalExtension();
        $fileName = sprintf('%s-%s.%s', Slugify::process($name), $now->format('U'), $extension);

        $folder = sprintf('%s%s/%s/%s/', $this->getTargetFolder(), $category, $now->format('Y'), $now->format('m'));

        $mimeType = $file->getMimeType();
        $file->move($folder, $fileName);

        if ($this->isResizeEnabled()) {
            $this->resize($mimeType, $category, $folder, $fileName);
        }

        return $this->calculatePath($folder.$fileName, self::PATH_RELATIVE);
    }

    /**
     * Get the base folder for web part.
     *
     * @return string
     */
    public function getBaseFolder()
    {
        return $this->config['base_folder'];
    }

    /**
     * Get the folder in which the upload must happen.
     *
     * @return string
     */
    public function getTargetFolder()
    {
        return $this->getBaseFolder().$this->config['storage_folder'];
    }

    /**
     * Check if the resize function is enabled.
     *
     * @return bool
     */
    public function isResizeEnabled()
    {
        return $this->config['resize'];
    }

    /**
     * Resize the given file.
     *
     * @param string $mimeType the mimeType of the file
     * @param string $category
     * @param string $folder
     * @param string $filename
     *
     * @throws \Exception
     */
    public function resize(string $mimeType, $category, $folder, $filename)
    {
        if (!in_array($mimeType, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))) {
            return;
        }
        if (!in_array($category, array_keys($this->config['image_formats']))) {
            return;
        }

        $this->resizeImage->process($folder.$filename, $category);
    }

    /**
     * Calculate the path based on the selected type.
     *
     * @param string $path     the path to process
     * @param string $pathType the type of path (PATH_RELATIVE or PATH_ABSOLUTE)
     *
     * @return string
     */
    public function calculatePath($path, $pathType)
    {
        if (self::PATH_RELATIVE === $pathType && 0 === stripos($path, $this->getBaseFolder())) {
            return str_ireplace($this->getBaseFolder(), '', $path);
        } elseif (self::PATH_ABSOLUTE === $pathType && false === stripos($path, $this->getBaseFolder())) {
            return $this->getBaseFolder().$path;
        }

        return $path;
    }

    /**
     * Delete the given File.
     *
     * @param string $filepath the path to the file to remove
     * @param string $category the category of the file
     */
    public function delete($filepath, $category)
    {
        $path = $this->calculatePath($filepath, self::PATH_ABSOLUTE);
        $otherFiles = $this->resizeImage->getResizedFiles($path, $category);

        foreach ($otherFiles as $tmp) {
            @unlink($this->calculatePath($tmp['path'], self::PATH_ABSOLUTE));
        }
        @unlink($path);
    }

    /**
     * Retrieve the version of the file if exist, otherwise, return the default one.
     *
     * @param string $version
     *
     * @return string
     */
    public function getVersion($filepath, $category, $version)
    {
        $versions = $this->resizeImage->getResizedFiles($filepath, $category);

        if (isset($versions[$version])) {
            return $versions[$version]['path'];
        }

        return $filepath;
    }

    /**
     * Retrieve the version of the file if exist, otherwise, return the default one.
     *
     * @param string $filepath   the path of the original file
     * @param string $category   the category of the file
     * @param string $pathFormat the path format to output (default : PATH_RELATIVE)
     *
     * @return array
     */
    public function getOtherVersions($filepath, $category, $pathFormat = self::PATH_RELATIVE)
    {
        $versions = $this->resizeImage->getResizedFiles($this->calculatePath($filepath, self::PATH_ABSOLUTE), $category);

        foreach (array_keys($versions) as $format) {
            $versions[$format]['path'] = $this->calculatePath($versions[$format]['path'], $pathFormat);
        }

        return $versions;
    }
}
