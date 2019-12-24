<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Twig;

use Darkanakin41\CoreBundle\Tools\Slugify;
use Darkanakin41\MediaBundle\Model\File;
use Darkanakin41\MediaBundle\Tools\File as ToolFile;
use Darkanakin41\MediaBundle\Tools\FileTools;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileInfo extends AbstractExtension
{
    const base_folder = 'media/';

    const EXTENSION_MAPPING = array(
        'css' => 'text/css',
        'twig' => 'text/html',
    );
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('fileinfo_image_dimensions', array($this, 'getImageDimensions')),
        );
    }

    public function getImageDimensions(File $file)
    {
        $dimensions = array();
        if (false === stripos($file->getFileType($file), 'image')) {
            return $dimensions;
        }
        global $kernel;
        $data = getimagesize($kernel->getProjectDir().'/public/'.$file->getFilepath());
        $dimensions = array('width' => $data[0], 'height' => $data[1]);

        return $dimensions;
    }

    public function getFileSize(File $file)
    {
        global $kernel;

        return filesize($kernel->getProjectDir().'/public/'.$file->getFilepath());
    }

    public function getFileType(File $file)
    {
        global $kernel;
        $fileExploded = explode('.', $file->getFilepath());
        $extension = array_pop($fileExploded);
        if (in_array($extension, array_keys(self::EXTENSION_MAPPING))) {
            return self::EXTENSION_MAPPING[$extension];
        }

        return mime_content_type($kernel->getProjectDir().'/public/'.$file->getFilepath());
    }

    public function getFileDate(File $file)
    {
        global $kernel;

        return \DateTime::createFromFormat('U', filemtime($kernel->getProjectDir().'/public/'.$file->getFilepath()));
    }

    public function refresh(File $file)
    {
        $file->setFilesize($this->getFileSize($file));
        $file->setFiletype($this->getFileType($file));
        $file->setDate($this->getFileDate($file));
    }

    public function upload(File $file_to_process, UploadedFile $to_upload = null)
    {
        if (is_null($to_upload)) {
            return $file_to_process;
        }
        $file = new ToolFile();
        $file->setFile($to_upload);
        $file->setFolder(self::base_folder);

        FileTools::setBaseFolder();
        $tools = new FileTools($file, Slugify::process($file_to_process->getFilename()));
        $tools->upload();

        $file_to_process->setFilepath($file->getFolder().$file->getFichier());
        $this->refresh($file_to_process);

        return $file;
    }

    public function getUrl(File $file)
    {
        return $this->packages->getUrl($file->getFilepath());
    }

    public function toArray(File $file)
    {
        $retour = array(
            'id' => $file->getId(),
            'filename' => $file->getFilename(),
            'filepath' => $this->packages->getUrl($file->getFilepath()),
            'filesize' => $file->getFilesize(),
            'filetype' => $file->getFiletype(),
            'dimensions' => $this->getImageDimensions($file),
        );

        return $retour;
    }
}
