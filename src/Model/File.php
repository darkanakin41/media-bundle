<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * File.
 *
 * @ORM\MappedSuperclass()
 */
abstract class File
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     * @ORM\Column(name="filetype", type="string", nullable=true)
     */
    private $filetype;

    /**
     * @var string
     * @ORM\Column(name="filesize", type="string", nullable=true)
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

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFiletype(): ?string
    {
        return $this->filetype;
    }

    public function setFiletype(string $filetype)
    {
        $this->filetype = $filetype;
    }

    public function getFilesize(): ?string
    {
        return $this->filesize;
    }

    public function setFilesize(string $filesize)
    {
        $this->filesize = $filesize;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

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

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }
}
