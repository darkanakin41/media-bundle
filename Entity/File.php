<?php

namespace PLejeune\MediaBundle\Entity;

use PLejeune\UserBundle\Entity\User;

/**
 * File
 */
class File
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $filepath;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $filetype;

    /**
     * @var string
     */
    private $filesize;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string|null
     */
    private $copyright;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var User
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFiletype(string $filetype): self
    {
        $this->filetype = $filetype;

        return $this;
    }

    public function getFiletype(): ?string
    {
        return $this->filetype;
    }

    public function setFilesize(string $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    public function getFilesize(): ?string
    {
        return $this->filesize;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCopyright(?string $copyright): self
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
