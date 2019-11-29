<?php
namespace Darkanakin41\MediaBundle\Tools;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of File
 *
 * @author Pierre
 */
class File {
    public function __construct(){}

    private $fichier;
    public function getFichier(){
        return $this->fichier;
    }
    public function setFichier($fichier){
        $this->fichier = $fichier;
    }

    private $file;
    public function getFile(){
        return $this->file;
    }
    public function setFile($fichier){
        $this->file = $fichier;
    }

    private $folder;
    public function getFolder(){
        return $this->folder;
    }
    public function setFolder($folder){
        $this->folder = $folder;
    }

}

?>
