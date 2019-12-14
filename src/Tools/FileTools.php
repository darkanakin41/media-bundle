<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Tools;

use Darkanakin41\MediaBundle\Model\File as Entity;

/**
 * Description of ToolsDocuments.
 */
class FileTools
{
    private static $baseFolder;
    /**
     * @var File
     */
    private $fichier;
    private $name;

    public function __construct(File $doc, $name = null)
    {
        $this->fichier = $doc;
        $this->name = $name;
        self::setBaseFolder();
    }

    public static function getBaseFolder()
    {
        return self::$baseFolder;
    }

    public static function setBaseFolder()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        self::$baseFolder = $kernel->getProjectDir().DIRECTORY_SEPARATOR.'public';
    }

    public static function delete(Entity $file)
    {
        unlink($file->getFilepath());
    }

    public function getFichier()
    {
        return $this->fichier;
    }

    public function getAbsolutePath()
    {
        return null === $this->fichier->getFichier() ? null : $this->getUploadRootDir().'/'.$this->fichier->getFichier();
    }

    public function getUploadRootDir()
    {
        return self::$baseFolder.'/'.$this->getUploadDir();
    }

    public function getWebPath()
    {
        return null === $this->fichier->getFichier() ? null : $this->getUploadDir().'/'.$this->fichier->getFichier();
    }

    public function upload()
    {
        if (null === $this->fichier->getFile()) {
            return;
        }

        $this->checkFolder();
        $filename = $this->fichier->getFile()->getClientOriginalName();
        if (!is_null($this->name)) {
            $extension = pathinfo($this->fichier->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);
            $filename = strtolower(sprintf('%s-%s.%s', $this->name, time(), $extension));
        }
        $this->fichier->getFile()->move($this->getUploadRootDir(), $filename);
        $this->fichier->setFichier($filename);
        $this->fichier->setFile(null);
    }

    public function download($url)
    {
        if (null === $this->fichier) {
            return;
        }

        $this->checkFolder();

        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = strtolower(sprintf('%s-%s.%s', $this->name, time(), $extension));

        $saveto = $this->getUploadRootDir().$filename;
        if (file_exists($saveto)) {
            unlink($saveto);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $raw = curl_exec($ch);
        curl_close($ch);

        $fp = fopen($saveto, 'x');
        fwrite($fp, $raw);
        fclose($fp);

        $this->fichier->setFichier($filename);
    }

    protected function getUploadDir()
    {
        return $this->fichier->getFolder();
    }

    private function checkFolder()
    {
        if (!file_exists($this->getUploadRootDir())) {
            mkdir($this->getUploadRootDir(), 0777, true);
        }
    }
}
