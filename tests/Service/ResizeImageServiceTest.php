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

    public function testGetResourceException()
    {
        $this->expectException(\Exception::class);
        $this->getService()->getResource($this->createFile("toto", "media/test.css"));
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
        $jpg = "https://news.efinancialcareers.com/binaries/content/gallery/efinancial-careers/articles/2019/04/github.jpg";
        $content = file_get_contents($jpg);
        $this->createFile($content, "media/FileUploadServiceTest.jpg");
        $png = "https://github.com/fluidicon.png";
        $content = file_get_contents($png);
        $this->createFile($content, "media/FileUploadServiceTest.png");
        $png2 = "https://itsocial.fr/wp-content/uploads/2018/06/github-696x437.png";
        $content = file_get_contents($png2);
        $this->createFile($content, "media/FileUploadServiceTest-2.png");
        $png3 = "https://user-images.githubusercontent.com/17478561/51830183-b8e16300-2308-11e9-9e87-02ba6f6c8f8e.png";
        $content = file_get_contents($png3);
        $this->createFile($content, "media/FileUploadServiceTest-3.png");
        $gif = "https://media.giphy.com/media/WvXuLOqJeJ0I0/giphy.gif";
        $content = file_get_contents($gif);
        $this->createFile($content, "media/FileUploadServiceTest.gif");

        return [[$jpg], [$png], [$png2], [$png3], [$gif]];
    }
}
