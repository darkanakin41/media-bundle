<?php

namespace Darkanakin41\MediaBundle\Tests\Service;

use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Darkanakin41\MediaBundle\Service\ResizeImageService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ResizeImageServiceTest extends WebTestCase
{

    /**
     * @dataProvider fileProvider
     */
    public function testGetResource($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $this->assertTrue(is_resource($resource));
    }

    /**
     * @return ResizeImageService
     */
    private function getService()
    {
        if (self::$container === null) {
            static::createClient();
        }

        $container = self::$container;
        /** @var ResizeImageService $service */
        $service = $container->get(ResizeImageService::class);
        return $service;
    }

    /**
     * @depends      testGetResource
     * @dataProvider fileProvider
     */
    public function testGetOriginalDimensions($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $dimensions = $this->getService()->getOriginalDimensions($resource);
        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey("width", $dimensions);
        $this->assertArrayHasKey("height", $dimensions);
    }

    /**
     * @depends      testGetResource
     * @dataProvider fileProvider
     */
    public function testGetFinalDimensionsExact($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $dimensions = $this->getService()->getFinalDimensions($resource, 800, 600, 'exact');
        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey("width", $dimensions);
        $this->assertEquals(800, $dimensions['width']);
        $this->assertArrayHasKey("height", $dimensions);
        $this->assertEquals(600, $dimensions['height']);
    }

    /**
     * @depends      testGetResource
     * @dataProvider fileProvider
     */
    public function testGetFinalDimensionsMaxWidth($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $dimensions = $this->getService()->getFinalDimensions($resource, 800, 600, 'maxwidth');
        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey("width", $dimensions);
        $this->assertEquals(800, $dimensions['width']);
        $this->assertArrayHasKey("height", $dimensions);
    }

    /**
     * @depends      testGetResource
     * @dataProvider fileProvider
     */
    public function testGetFinalDimensionsMaxHeight($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $dimensions = $this->getService()->getFinalDimensions($resource, 800, 600, 'maxheight');
        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey("width", $dimensions);
        $this->assertArrayHasKey("height", $dimensions);
        $this->assertEquals(600, $dimensions['height']);
    }

    /**
     * @depends      testGetResource
     * @dataProvider fileProvider
     */
    public function testGetFinalDimensionsDefault($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $originalDimensions = $this->getService()->getOriginalDimensions($resource);
        if ($originalDimensions['width'] > $originalDimensions['height']) {
            $dimensions = $this->getService()->getFinalDimensions($resource, 800, 600);
        } else {
            $dimensions = $this->getService()->getFinalDimensions($resource, 600, 800);
        }

        $this->assertIsArray($dimensions);
        $this->assertArrayHasKey("width", $dimensions);
        $this->assertArrayHasKey("height", $dimensions);

        if ($originalDimensions['width'] > $originalDimensions['height']) {
            $this->assertEquals(800, $dimensions['width']);
        } else {
            $this->assertEquals(800, $dimensions['height']);
        }
    }

    /**
     * @depends      testGetFinalDimensionsExact
     * @depends      testGetFinalDimensionsMaxWidth
     * @depends      testGetFinalDimensionsMaxHeight
     * @depends      testGetFinalDimensionsDefault
     * @dataProvider fileProvider
     */
    public function testResizeTo($filename)
    {
        $resource = $this->getService()->getResource($filename);
        $originalDimensions = $this->getService()->getOriginalDimensions($resource);
        $finalDimensions = $this->getService()->getFinalDimensions($resource, 800, 600, 'maxheight');
        $newResource = $this->getService()->resizeTo($resource, $originalDimensions['width'], $originalDimensions['height'], $finalDimensions['width'], $finalDimensions['height']);
        $this->assertTrue(is_resource($newResource));
        $this->assertNotEquals($resource, $newResource);
        $newDimensions = $this->getService()->getOriginalDimensions($newResource);
        $this->assertSame($finalDimensions, $newDimensions);
    }

    /**
     * @dataProvider fileProvider
     */
    public function testGetFileExtension($filename)
    {

        $filenameArray = explode('.', $filename);
        $extension = end($filenameArray);

        $this->assertEquals($extension, $this->getService()->getFileExtension($filename));
    }

    /**
     * @depends      testGetFileExtension
     * @dataProvider fileProvider
     */
    public function testGetResizedPath($filename)
    {
        $extension = $this->getService()->getFileExtension($filename);
        $newFilename = str_ireplace('.'.$extension, '-resized.'.$extension, $filename);

        $this->assertEquals($newFilename, $this->getService()->getResizedPath($filename, 'resized'));
    }

    /**
     * @depends      testResizeTo
     * @depends      testGetResizedPath
     * @dataProvider fileProvider
     */
    public function testSaveImage($filename)
    {
        $resource = $this->getService()->getResource($filename);

        $originalDimensions = $this->getService()->getOriginalDimensions($resource);
        $finalDimensions = $this->getService()->getFinalDimensions($resource, 800, 600, 'maxheight');
        $newResource = $this->getService()->resizeTo($resource, $originalDimensions['width'], $originalDimensions['height'], $finalDimensions['width'], $finalDimensions['height']);
        $this->assertTrue(is_resource($newResource));

        $newFilename = $this->getService()->getResizedPath($filename, 'resized');
        $this->getService()->saveImage($newResource, $newFilename, 100);

        $this->assertFileExists($newFilename);
    }

    /**
     * @depends      testResizeTo
     * @dataProvider fileProvider
     */
    public function testProcessUnknownFileException($filename)
    {
        $this->expectException(\Exception::class);
        $this->getService()->process($filename."totopdebe", "test");
    }

    /**
     * @depends      testResizeTo
     * @dataProvider fileProvider
     */
    public function testProcessUnknownResizeCategoryException($filename)
    {
        $this->expectException(\Exception::class);
        $this->getService()->process($filename, "test");
    }

    /**
     * @depends      testResizeTo
     * @dataProvider fileProvider
     */
    public function testProcess($filename)
    {
        $this->getService()->process($filename, 'banner');

        $extension = $this->getService()->getFileExtension($filename);

        foreach ($this->getBundleConfiguration()['image_formats']['banner'] as $format => $settings) {
            $resizeFileName = str_ireplace(sprintf('.%s', $extension), sprintf('-%s.%s', $format, $extension), $filename);
            $this->assertFileExists($resizeFileName);
        }
    }

    /**
     * @depends      testProcess
     * @dataProvider fileProvider
     */
    public function testGetResizedFiles($filename)
    {
        $this->getService()->process($filename, 'banner');

        $versions = $this->getService()->getResizedFiles($filename, 'banner');

        $this->assertEquals(count($this->getBundleConfiguration()['image_formats']['banner']), count($versions));

        foreach ($versions as $format => $version) {
            $this->assertFileExists($version['path']);
            if (isset($this->getBundleConfiguration()['image_formats']['banner']['min_width'])) {
                $this->assertFileExists($version['minWidth']);
            }
        }
    }

    /**
     * @depends      testProcess
     * @dataProvider fileProvider
     */
    public function testGetResizedFilesEmpty($filename)
    {
        $versions = $this->getService()->getResizedFiles($filename, 'test');
        $this->assertEmpty($versions);
    }

    public function testGetResourceException()
    {
        $this->expectException(\Exception::class);
        $this->getService()->getResource($this->createFile("toto", "media/testGetResourceException.css"));
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
        $jpg = $this->createFile($content, "media/ResizeImageServiceTest.jpg");

        $content = file_get_contents("https://github.com/fluidicon.png");
        $png = $this->createFile($content, "media/ResizeImageServiceTest.png");

        $content = file_get_contents("https://itsocial.fr/wp-content/uploads/2018/06/github-696x437.png");
        $png2 = $this->createFile($content, "media/ResizeImageServiceTest-2.png");

        $content = file_get_contents("https://user-images.githubusercontent.com/17478561/51830183-b8e16300-2308-11e9-9e87-02ba6f6c8f8e.png");
        $png3 = $this->createFile($content, "media/ResizeImageServiceTest-3.png");

        $content = file_get_contents("https://media.giphy.com/media/WvXuLOqJeJ0I0/giphy.gif");
        $gif = $this->createFile($content, "media/ResizeImageServiceTest.gif");

        return [[$jpg], [$png], [$png2], [$png3], [$gif]];
    }
}
