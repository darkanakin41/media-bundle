<?php

namespace Darkanakin41\MediaBundle\Tests\Twig;

use AppTestBundle\Entity\File;
use Darkanakin41\MediaBundle\Twig\FileExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class FileExtensionTest extends WebTestCase
{
    const FILTERS = [];
    const FUNCTIONS = ['darkanakin41_file_render'];

    /**
     * @return FileExtension
     */
    private function getService()
    {
        if (self::$container === null) {
            static::createClient();
        }

        $container = self::$container;
        /** @var FileExtension $service */
        $service = $container->get(FileExtension::class);
        return $service;
    }

    public function testGetFilters()
    {
        $service = $this->getService();
        $this->assertCount(count(self::FILTERS), $service->getFilters());

        foreach($service->getFilters() as $f){
            $this->assertTrue(in_array($f->getName(), self::FILTERS), $f->getName() . ' should not exist');
        }
    }

    public function testGetFunctions()
    {
        $service = $this->getService();
        $this->assertCount(count(self::FUNCTIONS), $service->getFunctions());

        foreach($service->getFunctions() as $f){
            $this->assertTrue(in_array($f->getName(), self::FUNCTIONS), $f->getName() . ' should not exist');
        }
    }

    public function testRenderUnknownType()
    {
        $service = $this->getService();

        $file = new File();
        $file->setFilename("toto");
        $file->setFiletype("unknown");

        $this->assertEmpty($service->render($file));
    }

    public function testRenderHTMLType()
    {
        $service = $this->getService();

        $file = new File();
        $file->setFilename("toto");
        $file->setFiletype("text/html");

        $output = $service->render($file);
        $this->assertNotEmpty($output);

        $crawler = new Crawler();
        $crawler->addHtmlContent($output);
        $this->assertEquals(1, $crawler->filter("div.darkanakin41-file.html5 i.fab.fa-html5")->count());
    }

    public function testRenderCSSType()
    {
        $service = $this->getService();

        $file = new File();
        $file->setFilename("toto");
        $file->setFiletype("text/css");

        $output = $service->render($file);
        $this->assertNotEmpty($output);

        $crawler = new Crawler();
        $crawler->addHtmlContent($output);
        $this->assertEquals(1, $crawler->filter("div.darkanakin41-file.css3 i.fab.fa-css3-alt")->count());
    }

    public function testRenderImageType()
    {
        $service = $this->getService();

        $file = new File();
        $file->setFilename("toto");
        $file->setFiletype("image/png");
        $file->setFilepath('https://github.com/fluidicon.png');
        $file->setCategory("test");

        $output = $service->render($file);
        $this->assertNotEmpty($output);

        $crawler = new Crawler();
        $crawler->addHtmlContent($output);
        $this->assertEquals(1, $crawler->filter("picture img")->count());
    }
}
