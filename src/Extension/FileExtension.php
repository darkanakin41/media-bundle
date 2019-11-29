<?php

namespace Darkanakin41\MediaBundle\Extension;


use Darkanakin41\MediaBundle\Entity\File;
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
     * @var \Darkanakin41\MediaBundle\Service\FileUpload
     */
    private $fileUpload;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->fileUpload = $container->get('Darkanakin41.media.fileupload');
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

    public function render(File $file, array $classes = [], string $title = null, $block = 'default')
    {
        if($title === null){
            $title = $file->getFilename();
        }
        if(empty($file->getFiletype())){
            $this->container->get('Darkanakin41.media.fileinfo')->refresh($file);
            $this->container->get('doctrine')->getManager()->persist($file);
            $this->container->get('doctrine')->getManager()->flush();
        }
        $template = null;
        $vars = ['file' => $file, 'classes' => $classes, 'title' => $title];
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
                $vars['versions'] = $this->fileUpload->getOtherFiles($file);
                break;
        }
        if($template !== null){
            return $template->renderBlock($block, $vars);
        }
        return '';
    }


}
