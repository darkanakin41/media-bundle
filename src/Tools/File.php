<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Tools;

/**
 * Description of File.
 *
 * @author Pierre
 */
class File
{
    private $fichier;

    private $file;

    private $folder;

    public function __construct()
    {
    }

    public function getFichier()
    {
        return $this->fichier;
    }

    public function setFichier($fichier)
    {
        $this->fichier = $fichier;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($fichier)
    {
        $this->file = $fichier;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
    }
}
