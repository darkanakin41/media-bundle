<?php

namespace PLejeune\MediaBundle\Service;

use PLejeune\MediaBundle\Entity\File;
use PLejeune\MediaBundle\Tools\File as ToolFile;
use PLejeune\MediaBundle\Tools\FileTools;
use PLejeune\MediaBundle\Tools\Slugify;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfo extends \Twig_Extension{

    CONST base_folder = "media/";
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function getName()
    {
        return 'FileInfo';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('fileinfo_image_dimensions', array($this, 'getImageDimensions')),
        );
    }

    public function getImageDimensions(File $file)
    {
        $dimensions = array();
        if (stripos($file->getFileType($file), "image") === FALSE) {
            return $dimensions;
        }
        global $kernel;
        $data = getimagesize($kernel->getProjectDir() . '/public/' . $file->getFilepath());
        $dimensions = array("width" => $data[0], "height" => $data[1]);
        return $dimensions;
    }

    public function getFileSize(File $file)
    {
        global $kernel;
        return filesize($kernel->getProjectDir() . '/public/' . $file->getFilepath());
    }

    public function getFileType(File $file)
    {
        global $kernel;
        return mime_content_type($kernel->getProjectDir() . '/public/' . $file->getFilepath());
    }

    public function getFileDate(File $file)
    {
        global $kernel;
        return \DateTime::createFromFormat("U", filemtime($kernel->getProjectDir() . '/public/' . $file->getFilepath()));
    }

    public function refresh(File $file)
    {
        $file->setFilesize($this->getFileSize($file));
        $file->setFiletype($this->getFileType($file));
        $file->setDate($this->getFileDate($file));
    }

    public function upload(File $file_to_process, UploadedFile $to_upload = NULL)
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

        $file_to_process->setFilepath($file->getFolder() . $file->getFichier());
        $this->refresh($file_to_process);

        return $file;
    }

    public function getUrl(File $file){
        return $this->packages->getUrl($file->getFilepath());
    }

    public function toArray(File $file)
    {
        $retour = array(
            "id" => $file->getId(),
            "filename" => $file->getFilename(),
            "filepath" => $this->packages->getUrl($file->getFilepath()),
            "filesize" => $file->getFilesize(),
            "filetype" => $file->getFiletype(),
            "dimensions" => $this->getImageDimensions($file),
        );
        return $retour;
    }
}