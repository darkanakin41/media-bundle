<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Twig;

use Darkanakin41\MediaBundle\Model\File;
use Darkanakin41\MediaBundle\Service\FileUploadService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var FileUploadService
     */
    private $fileUpload;
    /**
     * @var FileInfoExtension
     */
    private $fileInfo;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, FileUploadService $fileUpload, FileInfoExtension $fileInfo, Environment $twig)
    {
        $this->managerRegistry = $managerRegistry;
        $this->fileUpload = $fileUpload;
        $this->fileInfo = $fileInfo;
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('darkanakin41_file_render', array($this, 'render'), array('is_safe' => array('html'))),
        );
    }

    public function render(File $file, array $classes = array(), string $title = null, $block = 'default')
    {
        if (null === $title) {
            $title = $file->getFilename();
        }

        $template = null;
        $vars = array('file' => $file, 'classes' => $classes, 'title' => $title);
        switch ($file->getFiletype()) {
            case 'text/html':
                $template = $this->twig->load('@Darkanakin41Media/html.html.twig');
                break;
            case 'text/css':
                $template = $this->twig->load('@Darkanakin41Media/css.html.twig');
                break;
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/png':
                $template = $this->twig->load('@Darkanakin41Media/image.html.twig');
                $vars['versions'] = $this->fileUpload->getOtherVersions($file->getFilepath(), $file->getCategory());
                break;
        }
        if (null !== $template) {
            return $template->renderBlock($block, $vars);
        }

        return '';
    }
}
