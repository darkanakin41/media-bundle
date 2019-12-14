<?php

namespace Darkanakin41\MediaBundle\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * File
 *
 * @ORM\MappedSuperclass()
 */
abstract class File
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="filepath", type="string")
     */
    private $filepath;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string")
     */
    private $filename;

    /**
     * @var string
     * @ORM\Column(name="filetype", type="string")
     */
    private $filetype;

    /**
     * @var string
     * @ORM\Column(name="filesize", type="string")
     */
    private $filesize;

    /**
     * @var string
     * @ORM\Column(name="category", type="string")
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(name="copyright", type="string", nullable=true)
     */
    private $copyright;

    /**
     * @var DateTime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFilepath(): string
    {
        return $this->filepath;
    }

    /**
     * @param string $filepath
     */
    public function setFilepath(string $filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFiletype(): string
    {
        return $this->filetype;
    }

    /**
     * @param string $filetype
     */
    public function setFiletype(string $filetype)
    {
        $this->filetype = $filetype;
    }

    /**
     * @return string
     */
    public function getFilesize(): string
    {
        return $this->filesize;
    }

    /**
     * @param string $filesize
     */
    public function setFilesize(string $filesize)
    {
        $this->filesize = $filesize;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }

    /**
     * @return string|null
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param string|null $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

}
