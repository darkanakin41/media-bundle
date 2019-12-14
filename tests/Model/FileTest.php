<?php

namespace Darkanakin41\MediaBundle\Tests\Model;


use Darkanakin41\MediaBundle\Model\File;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    public function testId(){
        $entity = $this->getFile();
        $this->assertNull($entity->getId());
    }

    public function testFilepath(){
        $entity = $this->getFile();

        $entity->setFilepath("toto");
        $this->assertEquals("toto", $entity->getFilepath());
    }

    public function testFilename(){
        $entity = $this->getFile();

        $entity->setFilename("toto");
        $this->assertEquals("toto", $entity->getFilename());
    }

    public function testFiletype(){
        $entity = $this->getFile();

        $entity->setFiletype("toto");
        $this->assertEquals("toto", $entity->getFiletype());
    }

    public function testFilesize(){
        $entity = $this->getFile();

        $entity->setFilesize("toto");
        $this->assertEquals("toto", $entity->getFilesize());
    }

    public function testCategory(){
        $entity = $this->getFile();

        $entity->setCategory("toto");
        $this->assertEquals("toto", $entity->getCategory());
    }

    public function testCopyright(){
        $entity = $this->getFile();
        $this->assertNull($entity->getCopyright());

        $entity->setCopyright("toto");
        $this->assertEquals("toto", $entity->getCopyright());
    }

    public function testDate(){
        $entity = $this->getFile();
        $this->assertNotNull($entity->getDate());

        $now = new \DateTime();
        $format = "Y-m-d H:i:s";

        $this->assertEquals($now->format($format), $entity->getDate()->format($format));

    }

    /**
     * @return File
     */
    protected function getFile()
    {
        return $this->getMockForAbstractClass(File::class);
    }

}
