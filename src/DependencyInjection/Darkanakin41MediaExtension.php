<?php

namespace Darkanakin41\MediaBundle\DependencyInjection;


use Darkanakin41\MediaBundle\Service\FileUpload;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Darkanakin41MediaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('darkanakin41.media.config', $processedConfig);
        $definition = $container->getDefinition(FileUpload::class);
        $definition->replaceArgument(0, $processedConfig);
    }

    public function prepend(ContainerBuilder $container)
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $container->prependExtensionConfig('twig', array('paths' => array(__DIR__.'/../Resources/views' => "Darkanakin41Media")));
    }

}
