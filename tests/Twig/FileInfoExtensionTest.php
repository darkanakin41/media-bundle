<?php

namespace Darkanakin41\MediaBundle\Tests\Twig;

use AppTestBundle\Entity\File;
use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Darkanakin41\MediaBundle\Twig\FileInfoExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileInfoExtensionTest extends WebTestCase
{
    const FILTERS = [];
    const FUNCTIONS = ['darkanakin41_file_info_image_dimensions'];


    public function testGetFilters()
    {
        $service = $this->getService();
        $this->assertCount(count(self::FILTERS), $service->getFilters());

        foreach ($service->getFilters() as $f) {
            $this->assertTrue(in_array($f->getName(), self::FILTERS), $f->getName().' should not exist');
        }
    }

    /**
     * @return FileInfoExtension
     */
    private function getService()
    {
        if (self::$container === null) {
            static::createClient();
        }

        /** @var FileInfoExtension $service */
        $service = self::$container->get(FileInfoExtension::class);
        return $service;
    }

    public function testGetFunctions()
    {
        $service = $this->getService();
        $this->assertCount(count(self::FUNCTIONS), $service->getFunctions());

        foreach ($service->getFunctions() as $f) {
            $this->assertTrue(in_array($f->getName(), self::FUNCTIONS), $f->getName().' should not exist');
        }
    }

    public function testGetFullPath()
    {
        $file = new File();
        $file->setFilepath("toto.png");

        $service = $this->getService();
        $this->assertNotEmpty($service->getFullPath($file));
    }

    /**
     * @depends testGetFullPath
     */
    public function testGetImageDimensions()
    {
        $file = new File();
        $file->setFiletype("image");
        $file->setFilepath("media/FileInfoExtensionTest.testGetImageDimensions.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $image = $service->getFullPath($file);
        $dimensions = getimagesize($image);

        $this->assertNotEmpty($service->getImageDimensions($file));
        $this->assertSame(['width' => $dimensions[0], 'height' => $dimensions[1]], $service->getImageDimensions($file));
    }

    /**
     * @return array
     */
    private function getBundleConfiguration()
    {
        if (self::$container === null) {
            static::createClient();
        }

        /** @var ParameterBagInterface $service */
        $service = self::$container->get(ParameterBagInterface::class);
        return $service->get(Darkanakin41MediaExtension::CONFIG_KEY);
    }

    /**
     * @depends testGetFullPath
     */
    public function testGetFileDate()
    {
        $file = new File();
        $file->setFiletype("image");
        $file->setFilepath("media/FileInfoExtensionTest.testGetFileDate.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $image = $service->getFullPath($file);
        $date = \DateTime::createFromFormat('U', filemtime($image));

        $this->assertNotEmpty($service->getImageDimensions($file));
        $this->assertSame($date->format('U'), $service->getFileDate($file)->format('U'));
    }

    /**
     * @depends testGetFullPath
     */
    public function testGetFileSize()
    {
        $file = new File();
        $file->setFiletype("image");
        $file->setFilepath("media/FileInfoExtensionTest.testGetFileSize.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $image = $service->getFullPath($file);
        $size = filesize($image);

        $this->assertNotEmpty($service->getImageDimensions($file));
        $this->assertSame($size, $service->getFileSize($file));
    }

    public function testGetImageDimensionsNotImage()
    {
        $file = new File();
        $file->setFiletype("other");

        $service = $this->getService();
        $this->assertEmpty($service->getImageDimensions($file));
    }

    /**
     * @param $filepath
     * @param $expected
     *
     * @dataProvider getFileTypeProvider
     */
    public function testGetFileType($filepath, $expected){
        $file = new File();
        $file->setFilepath($filepath);

        $service = $this->getService();

        $this->assertSame($expected, $service->getFileType($file));
    }

    public function getFileTypeProvider(){
        $data = [];

        $baseFilepath = "media/FileInfoExtensionTest.getFileTypeProvider";

        foreach(FileInfoExtension::EXTENSION_MAPPING as $extension => $type){
            $filename = $baseFilepath . "." . $extension;
            $content = "ah coucou";
            $this->createFile($content, $filename);
            $data[] = [$filename, $type];
        }

        $filename = $baseFilepath . ".png";
        $this->createImageFile($filename);
        $data[] = [$filename, "image/png"];

        return $data;
    }

    /**
     * @depends testGetFileSize
     * @depends testGetFileType
     * @depends testGetFileDate
     */
    public function testRefresh(){
        $file = new File();
        $file->setFilepath("media/FileInfoExtensionTest.testRefresh.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $service->refresh($file);

        $this->assertEquals($service->getFileSize($file), $file->getFilesize());
        $this->assertEquals($service->getFileType($file), $file->getFiletype());
        $this->assertEquals($service->getFileDate($file), $file->getDate());
    }

    public function testGetUrl(){
        $file = new File();
        $file->setFilepath("media/FileInfoExtensionTest.testGetUrl.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $this->assertEquals("/".$file->getFilepath(), $service->getUrl($file));
    }

    public function testToArray(){
        $file = new File();
        $file->setFilename("FileInfoExtensionTest.testToArray");
        $file->setFilepath("media/FileInfoExtensionTest.testToArray.png");

        $this->createImageFile($file->getFilepath());

        $service = $this->getService();

        $expected = [
            'id' => $file->getId(),
            'filename' => $file->getFilename(),
            'filepath' => $service->getUrl($file),
            'filesize' => $file->getFilesize(),
            'filetype' => $file->getFiletype(),
            'dimensions' => $service->getImageDimensions($file),
        ];

        $this->assertSame($expected, $service->toArray($file));
    }

    /**
     * Create the file with $content on the filesystem
     *
     * @param $content
     * @param $filename
     *
     * @return string
     */
    private function createFile($content, $filename)
    {
        $fp = fopen($this->getBundleConfiguration()['base_folder'].$filename, 'w+');
        fwrite($fp, $content);
        fclose($fp);

        return $this->getBundleConfiguration()['base_folder'].$filename;
    }

    /**
     * Create the image file with $content on the filesystem
     *
     * @param $filename
     *
     * @return string
     */
    private function createImageFile($filename)
    {
        $content = file_get_contents('https://github.com/fluidicon.png');
        return $this->createFile($content, $filename);
    }

}
