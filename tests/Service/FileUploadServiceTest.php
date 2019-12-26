<?php

namespace Darkanakin41\MediaBundle\Tests\Service;

use Darkanakin41\CoreBundle\Tools\Slugify;
use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Darkanakin41\MediaBundle\Service\FileUploadService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadServiceTest extends WebTestCase
{

    /**
     * @return FileUploadService
     */
    private function getService()
    {
        if (self::$container === null) {
            static::createClient();
        }

        $container = self::$container;
        /** @var FileUploadService $service */
        $service = $container->get(FileUploadService::class);
        return $service;
    }

    public function testGetBaseFolder()
    {
        $expected = $this->getBundleConfiguration()['base_folder'];
        $this->assertEquals($expected, $this->getService()->getBaseFolder());
    }

    /**
     * @depends testGetBaseFolder
     */
    public function testGetTargetFolder()
    {
        $expected = $this->getService()->getBaseFolder().$this->getBundleConfiguration()['storage_folder'];
        $this->assertEquals($expected, $this->getService()->getTargetFolder());
    }

    public function testIsResizeEnabled()
    {
        $expected = $this->getBundleConfiguration()['resize'];
        $this->assertEquals($expected, $this->getService()->isResizeEnabled());
    }

    /**
     * @depends      testGetBaseFolder
     * @dataProvider fileProvider
     */
    public function testCalculatePath($filename)
    {
        $this->assertEquals($filename, $this->getService()->calculatePath($filename, FileUploadService::PATH_ABSOLUTE));

        $relative = str_ireplace($this->getBundleConfiguration()['base_folder'], '', $filename);

        $this->assertEquals($relative, $this->getService()->calculatePath($filename, FileUploadService::PATH_RELATIVE));

        $this->assertEquals($relative, $this->getService()->calculatePath($relative, FileUploadService::PATH_RELATIVE));

        $this->assertEquals($filename, $this->getService()->calculatePath($relative, FileUploadService::PATH_ABSOLUTE));
    }

    /**
     * @depends testGetTargetFolder
     * @depends testIsResizeEnabled
     */
    public function testUploadAndDelete()
    {
        $path = $this->createFile("toto", "toto.css");
        $uploadedFile = new UploadedFile($path, "toto.css", null, UPLOAD_ERR_OK, true);
        $filepath = $this->getService()->upload($uploadedFile, 'FileUploadServiceTest::testUpload.css', 'style');

        $finalAbsolutePath = $this->getService()->calculatePath($filepath, FileUploadService::PATH_ABSOLUTE);
        $this->assertFileExists($finalAbsolutePath);
        $this->assertStringContainsString('/style/', $filepath);
        $this->assertStringContainsString('/'.Slugify::process('FileUploadServiceTest::testUpload.css'), $filepath);

        $this->getService()->delete($finalAbsolutePath, 'style');
        $this->assertFileNotExists($finalAbsolutePath);
    }

    /**
     * @depends      testUploadAndDelete
     * @dataProvider fileProvider
     */
    public function testUploadAndDeleteImage($filepath, $category)
    {
        $filepathExploded = explode("/", $filepath);
        $filename = end($filepathExploded);
        $uploadedFile = new UploadedFile($filepath, $filename, null, UPLOAD_ERR_OK, true);
        $filepath = $this->getService()->upload($uploadedFile, $filename, $category);

        $finalAbsolutePath = $this->getService()->calculatePath($filepath, FileUploadService::PATH_ABSOLUTE);
        $this->assertFileExists($finalAbsolutePath);
        $this->assertStringContainsString('/'.$category.'/', $filepath);
        $this->assertStringContainsString('/'.Slugify::process($filename), $filepath);

        $versions = $this->getService()->getOtherVersions($finalAbsolutePath, $category, FileUploadService::PATH_ABSOLUTE);
        if (isset($this->getBundleConfiguration()['image_formats'][$category])) {
            $this->assertCount(count($this->getBundleConfiguration()['image_formats'][$category]), $versions);
        } else {
            $this->assertCount(0, $versions);
        }

        foreach ($versions as $version => $params) {
            $versionPath = $this->getService()->getVersion($finalAbsolutePath, $category, $version);
            $this->assertNotEquals($finalAbsolutePath, $versionPath);
            $this->assertEquals($params['path'], $versionPath);
        }

        $versionPath = $this->getService()->getVersion($finalAbsolutePath, $category, 'thisisnotavalidversion');
        $this->assertEquals($finalAbsolutePath, $versionPath);

        $this->getService()->delete($finalAbsolutePath, $category);
        $this->assertFileNotExists($finalAbsolutePath);

        foreach ($versions as $version) {
            $this->assertFileNotExists($version['path']);
        }
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

    public function fileProvider()
    {
        $content = file_get_contents("https://news.efinancialcareers.com/binaries/content/gallery/efinancial-careers/articles/2019/04/github.jpg");
        $jpg = $this->createFile($content, "media/FileUploadServiceTest.jpg");

        $content = file_get_contents("https://github.com/fluidicon.png");
        $png = $this->createFile($content, "media/FileUploadServiceTest.png");

        $content = file_get_contents("https://itsocial.fr/wp-content/uploads/2018/06/github-696x437.png");
        $png2 = $this->createFile($content, "media/FileUploadServiceTest-2.png");

        $content = file_get_contents("https://user-images.githubusercontent.com/17478561/51830183-b8e16300-2308-11e9-9e87-02ba6f6c8f8e.png");
        $png3 = $this->createFile($content, "media/FileUploadServiceTest-3.png");

        $content = file_get_contents("https://media.giphy.com/media/WvXuLOqJeJ0I0/giphy.gif");
        $gif = $this->createFile($content, "media/FileUploadServiceTest.gif");

        return [[$jpg, "banner"], [$png, "avatar"], [$png2, "banner"], [$png3, "banner"], [$gif, "notexistingcategory"]];
    }
}
