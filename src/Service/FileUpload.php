<?php

namespace Darkanakin41\MediaBundle\Service;

use Darkanakin41\MediaBundle\Entity\File;
use Darkanakin41\MediaBundle\Tools\ResizeImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Darkanakin41\CoreBundle\Tools\Slugify;

class FileUpload
{
    public CONST PATH_RELATIVE = 'relative';
    public CONST PATH_ABSOLUTE = 'absolute';

    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $rootFolder;

    /**
     * FileUpload constructor.
     * @param array $config
     * @param string $rootFolder
     */
    public function __construct(array $config, $rootFolder)
    {
        $this->config = $config;
        $this->rootFolder = $rootFolder;
    }

    public function getRootFolder(){
        return $this->rootFolder;
    }


    public function getName()
    {
        return 'FileUpload';
    }

    /**
     * Upload the file in the target folder
     *
     * @param UploadedFile $file
     * @param string $name
     * @param string $category
     *
     * @return string
     * @throws \Exception
     */
    public function upload(UploadedFile $file, $name = "image", $category = '')
    {
        $now = new \DateTime('now');
        $extension = $file->getClientOriginalExtension();
        $fileName = sprintf('%s-%s.%s', Slugify::process($name), time(), $extension);
        $folder = sprintf('%s%s/%s/%s/', $this->config['storage_folder'], $category, $now->format('Y'), $now->format('m'));
        $mimeType = $file->getMimeType();
        $file->move($folder, $fileName);

        if(in_array($mimeType, ['image/jpg','image/jpeg','image/gif','image/png']) && in_array($category, array_keys($this->config['image_formats']))){
            $resizer = new ResizeImage($folder . $fileName);
            foreach($this->config['image_formats'][$category] as $key => $values){
                $quality = 90;
                if(isset($values['quality'])){
                    $quality = $values['quality'];
                }
                $resizer->resizeTo($values['width'], $values['height'], $values['resize']);
                $resizeFileName = sprintf('%s-%s-%s.%s', Slugify::process($name), time(), $key, $extension);
                $resizer->saveImage($folder . DIRECTORY_SEPARATOR . $resizeFileName, $quality);
            }
        }

        return $this->calculatePath($folder . $fileName, self::PATH_RELATIVE);
    }

    public function delete(File $file){
        $path = $file->getFilepath();

        $otherFiles = $this->getOtherFiles($file);
        foreach($otherFiles as $tmp){
            @unlink($this->calculatePath($tmp['path'], self::PATH_ABSOLUTE));
        }
        @unlink($this->calculatePath($path, self::PATH_ABSOLUTE));
    }

    public function getOtherFiles(File $file){
        $mimeType = $file->getFiletype();
        $category = $file->getCategory();
        $path = $file->getFilepath();

        $retour = [];
        if(in_array($mimeType, ['image/jpg','image/jpeg','image/gif','image/png']) && in_array($category, array_keys($this->config['image_formats']))){
            foreach($this->config['image_formats'][$category] as $key => $values){
                $infoParts = pathinfo($path);
                $resizedPath = str_ireplace(".".$infoParts['extension'], sprintf('-%s.%s', $key, $infoParts['extension']), $path);
                if(file_exists($this->calculatePath($resizedPath, self::PATH_ABSOLUTE))){
                    $data = ['path' => $resizedPath];
                    if(isset($values['min_width'])){
                        $data += ['minWidth' => $values['min_width']];
                    }
                    $retour[$key] = $data;
                }
            }
        }

        return $retour;
    }

    public function calculatePath($path, $pathType){
        if($pathType === self::PATH_RELATIVE && stripos($path, $this->getRootFolder()) === 0){
            return str_ireplace($this->getRootFolder() . '/public/', '',$path);
        }elseif($pathType === self::PATH_ABSOLUTE && stripos($path, $this->getRootFolder()) === FALSE){
            return $this->getRootFolder() . '/public/' . $path;
        }
        return $path;
    }

    /**
     * Retrieve the version of the file if exist, otherwise, return the default one
     *
     * @param File $file
     * @param string $version
     *
     * @return string
     */
    public function getVersion(File $file, $version){
        $versions = $this->getOtherFiles($file);
        if(in_array($version, array_keys($versions))) return $versions[$version]['path'];
        return $file->getFilepath();
    }


}
