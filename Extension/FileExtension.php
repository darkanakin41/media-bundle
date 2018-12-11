<?php

namespace PLejeune\MediaBundle\Extension;


use PLejeune\MediaBundle\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Twig\Environment
     */
    private $twig;
    /**
     * @var \PLejeune\MediaBundle\Service\FileUpload
     */
    private $fileUpload;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->fileUpload = $container->get('plejeune.media.fileupload');
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('file_render', [$this, 'render'], ['is_safe' => ['html']]),
        );
    }

    public function render(File $file, array $classes = [], string $title = null)
    {
        if($title === null){
            $title = $file->getFilename();
        }
        if(empty($file->getFiletype())){
            $this->container->get('plejeune.media.fileinfo')->refresh($file);
            $this->container->get('doctrine')->getManager()->persist($file);
            $this->container->get('doctrine')->getManager()->flush();
        }
        $template = null;
        $vars = ['file' => $file, 'classes' => $classes, 'title' => $title];
        switch ($file->getFiletype()) {
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/png':
                $template = $this->twig->load('@PLejeuneMedia/image.html.twig');
                $vars['versions'] = $this->fileUpload->getOtherFiles($file);
                break;
        }
        if($template !== null){
            return $template->render($vars);
        }
        return '';
    }


}