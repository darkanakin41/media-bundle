<?php

namespace Darkanakin41\MediaBundle\Tests\Model;


use Darkanakin41\CoreBundle\Tests\Model\AbstractEntityTestCase;
use Darkanakin41\MediaBundle\Model\File;

class StreamTest extends AbstractEntityTestCase
{

    public function notNullableFieldProvider()
    {
        return [
            ['filename', 'toto'],
            ['filepath', 'toto'],
            ['filetype', 'toto'],
            ['filesize', 'toto'],
            ['category', 'toto'],
            ['date', new \DateTime()],
        ];
    }

    public function nullableFieldProvider()
    {
        return [
            ['copyright', 'toto']
        ];
    }

    public function defaultValueProvider()
    {
        return [
        ];
    }

    /**
     * @return File
     */
    protected function getEntity()
    {
        return $this->getMockForAbstractClass(File::class);
    }


    public function testDate(){
        $entity = $this->getEntity();
        $this->assertNotNull($entity->getDate());

        $now = new \DateTime();
        $format = "Y-m-d H:i:s";

        $this->assertEquals($now->format($format), $entity->getDate()->format($format));
    }

}
